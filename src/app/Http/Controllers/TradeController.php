<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\TradeMessage;
use App\Http\Requests\TradeMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\TradeCompletedMail;

class TradeController extends Controller
{
    /**
     * 取引チャット画面を表示
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\View\View
     */
    public function show(Purchase $purchase)
    {
        $user = Auth::user();

        // アクセス権限チェック（購入者または出品者のみ）
        if ($purchase->user_id !== $user->id && $purchase->item->user_id !== $user->id) {
            abort(403, 'この取引にアクセスする権限がありません。');
        }

        // メッセージを既読にする（自分以外が送信したもの）
        $purchase->tradeMessages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // メッセージ一覧を取得
        $messages = $purchase->tradeMessages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // 他の取引一覧を取得（サイドバー用）
        $otherTrades = $this->getOtherTrades($user, $purchase);

        // セッションから入力保持データを取得
        $oldMessage = session('trade_message_input.' . $purchase->id);

        return view('trades.show', compact('purchase', 'messages', 'otherTrades', 'oldMessage'));
    }

    /**
     * メッセージを投稿
     *
     * @param  \App\Http\Requests\TradeMessageRequest  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMessage(TradeMessageRequest $request, Purchase $purchase)
    {
        $user = Auth::user();

        // アクセス権限チェック
        if ($purchase->user_id !== $user->id && $purchase->item->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();

        // 画像アップロード処理
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        // メッセージ作成
        TradeMessage::create([
            'purchase_id' => $purchase->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'image_path' => $imagePath,
        ]);

        // セッションから入力保持データをクリア
        session()->forget('trade_message_input.' . $purchase->id);

        return redirect()->route('trades.show', $purchase)
            ->with('success', 'メッセージを送信しました。');
    }

    /**
     * メッセージを編集
     *
     * @param  \App\Http\Requests\TradeMessageRequest  $request
     * @param  \App\Models\TradeMessage  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMessage(TradeMessageRequest $request, TradeMessage $message)
    {
        $user = Auth::user();

        // 送信者本人のみ編集可能
        if ($message->sender_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();

        // 画像アップロード処理
        if ($request->hasFile('image')) {
            // 古い画像を削除
            if ($message->image_path) {
                Storage::disk('public')->delete($message->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('trade_messages', 'public');
        }

        $message->update([
            'message' => $validated['message'],
            'image_path' => $validated['image_path'] ?? $message->image_path,
        ]);

        return redirect()->route('trades.show', $message->purchase)
            ->with('success', 'メッセージを編集しました。');
    }

    /**
     * メッセージを削除
     *
     * @param  \App\Models\TradeMessage  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyMessage(TradeMessage $message)
    {
        $user = Auth::user();

        // 送信者本人のみ削除可能
        if ($message->sender_id !== $user->id) {
            abort(403);
        }

        $purchaseId = $message->purchase_id;

        // 画像を削除
        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->delete();

        return redirect()->route('trades.show', $purchaseId)
            ->with('success', 'メッセージを削除しました。');
    }

    /**
     * 入力情報を保持（画面遷移時）
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveInput(Request $request, Purchase $purchase)
    {
        session(['trade_message_input.' . $purchase->id => $request->input('message')]);

        return response()->json(['success' => true]);
    }

    /**
     * 取引を完了する（購入者のみ）
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Purchase $purchase)
    {
        $user = Auth::user();

        // 購入者のみ完了可能
        if ($purchase->user_id !== $user->id) {
            abort(403, '購入者のみが取引を完了できます。');
        }

        // 既に完了済みの場合
        if ($purchase->is_completed) {
            return redirect()->route('trades.show', $purchase)
                ->with('error', 'この取引は既に完了しています。');
        }

        // 取引完了
        $purchase->complete();

        // FN016: 出品者に取引完了メールを送信
        $seller = $purchase->item->user;
        Mail::to($seller->email)->send(new TradeCompletedMail($purchase));

        // 評価画面へリダイレクト（モーダル表示）
        return redirect()->route('trades.show', $purchase)
            ->with('show_rating_modal', true);
    }

    /**
     * 他の取引一覧を取得（サイドバー用）
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Purchase  $currentPurchase
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getOtherTrades($user, $currentPurchase)
    {
        // 購入した商品の取引
        $purchases = Purchase::with(['item', 'tradeMessages' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->where('user_id', $user->id)
            ->inProgress()
            ->where('id', '!=', $currentPurchase->id)
            ->get();

        // 出品した商品の取引
        $sales = Purchase::with(['item', 'user', 'tradeMessages' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->whereHas('item', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->inProgress()
            ->where('id', '!=', $currentPurchase->id)
            ->get();

        // 結合してソート
        return $purchases->concat($sales)->sortByDesc(function ($purchase) {
            return $purchase->latest_message ? $purchase->latest_message->created_at : $purchase->created_at;
        });
    }
}
