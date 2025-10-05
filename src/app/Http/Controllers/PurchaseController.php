<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;

/**
 * 購入コントローラー
 * PG06: 商品購入画面
 * PG07: 送付先住所変更画面
 */
class PurchaseController extends Controller
{
    /**
     * 商品購入画面表示 (PG06)
     * FN021: 購入前商品情報取得機能
     * 
     * @param int $item_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($item_id)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::with(['user', 'condition', 'categories'])->findOrFail($item_id);

        // 既に購入済みの場合
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        // 自分の商品は購入不可
        if ($item->user_id === Auth::id()) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', '自分の商品は購入できません。');
        }

        // プロフィール情報から住所を初期値として取得
        $profile = Auth::user()->profile;

        // 支払い方法の選択肢
        $paymentMethods = [
            'convenience' => 'コンビニ支払い',
            'card' => 'カード支払い',
        ];

        return view('purchase.show', compact('item', 'profile', 'paymentMethods'));
    }

    /**
     * 商品購入処理
     * FN022: 商品購入機能
     * FN023: 支払い方法選択機能(Stripe)
     * 
     * @param PurchaseRequest $request
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PurchaseRequest $request, $item_id)
    {
        // バリデーション      
        $item = Item::findOrFail($item_id);

        // 購入済みチェック
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        // ユーザーのプロファイルから住所を取得
        $profile = Auth::user()->profile;

        if (!$profile || !$profile->postal_code || !$profile->address) {
            return redirect()
                ->route('purchase.address', $item_id)
                ->with('error', '配送先を設定してください。');
        }

        $paymentMethod = $request->payment_method;

        // カード支払いの場合
        if ($paymentMethod === 'card') {
            $validated = [
                'postal_code' => $profile->postal_code,
                'address' => $profile->address,
                'building' => $profile->building,
                'payment_method' => $paymentMethod,
            ];

            return $this->createStripeCheckout($item, $validated);
        }

        // コンビニ支払いの場合
        Purchase::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'postal_code' => $profile->postal_code,
            'address' => $profile->address,
            'building' => $profile->building,
            'payment_method' => $paymentMethod,
        ]);

        $item->update(['is_sold' => true]);

        return redirect()
            ->route('items.index')
            ->with('success', '商品を購入しました。');
    }

    /**
     * Stripe Checkout セッション作成
     */
    private function createStripeCheckout($item, $validated)
    {
        // Stripe APIキーを設定
        Stripe::setApiKey(config('stripe.secret_key'));

        try {
            // 住所情報をセッションに一時保存
            session([
                'purchase_address' => [
                    'postal_code' => $validated['postal_code'],
                    'address' => $validated['address'],
                    'building' => $validated['building'] ?? null,
                ]
            ]);

            // Stripe Checkout セッション作成
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                            'description' => mb_substr($item->description, 0, 100),
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['item_id' => $item->id]),
                'cancel_url' => route('purchase.cancel', ['item_id' => $item->id]),
            ]);

            // Stripe決済画面へリダイレクト
            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '決済画面への接続に失敗しました。');
        }
    }

    /**
     * Stripe決済成功時の処理
     */
    public function success($item_id)
    {
        $item = Item::findOrFail($item_id);

        // セッションから住所情報を取得
        $addressData = session('purchase_address');

        if (!$addressData) {
            return redirect()
                ->route('purchase.show', $item_id)
                ->with('error', '購入情報が見つかりません。');
        }

        // 購入情報を保存
        Purchase::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'postal_code' => $addressData['postal_code'],
            'address' => $addressData['address'],
            'building' => $addressData['building'] ?? null,
            'payment_method' => 'card',
        ]);

        $item->update(['is_sold' => true]);

        // セッションクリア
        session()->forget('purchase_address');

        return redirect()
            ->route('items.index')
            ->with('success', '商品を購入しました。');
    }

    /**
     * Stripe決済キャンセル時の処理
     */
    public function cancel($item_id)
    {
        session()->forget('purchase_address');

        return redirect()
            ->route('purchase.show', $item_id)
            ->with('error', '決済がキャンセルされました。');
    }


    /**
     * 配送先住所変更画面表示 (PG07)
     * FN024: 配送先変更機能
     * 
     * @param int $item_id
     * @return \Illuminate\View\View
     */
    public function editAddress($item_id)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($item_id);

        // 既に購入済みの場合
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        $profile = Auth::user()->profile;

        return view('purchase.address', compact('item', 'profile'));
    }

    /**
     * 配送先住所更新処理
     * FN024: 配送先変更機能
     * 
     * @param AddressRequest $request
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAddress(AddressRequest $request, $item_id)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($item_id);
        $validated = $request->validated();

        // プロフィール情報を更新（購入時に使用）
        $profile = Auth::user()->profile;
        if ($profile) {
            $profile->update([
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'building' => $validated['building'] ?? null,
            ]);
        } else {
            // プロフィールが存在しない場合は作成
            Auth::user()->profile()->create([
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'building' => $validated['building'] ?? null,
            ]);
        }

        // FN024要件: 住所変更後は商品購入画面に戻る
        return redirect()
            ->route('purchase.show', $item_id)
            ->with('success', '配送先住所を変更しました。');
    }
}
