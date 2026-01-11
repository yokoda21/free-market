<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Rating;
use App\Http\Requests\RatingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TradeCompletedMail;

class RatingController extends Controller
{
    /**
     * 評価を送信
     *
     * @param  \App\Http\Requests\RatingRequest  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RatingRequest $request, Purchase $purchase)
    {
        $user = Auth::user();
        
        // 取引が完了していない場合
        if (!$purchase->is_completed) {
            return redirect()->route('trades.show', $purchase)
                ->with('error', '取引が完了していないため、評価できません。');
        }
        
        // 購入者または出品者かチェック
        $isBuyer = $purchase->user_id === $user->id;
        $isSeller = $purchase->item->user_id === $user->id;
        
        if (!$isBuyer && !$isSeller) {
            abort(403, 'この取引に関係していないため、評価できません。');
        }
        
        // 既に評価済みかチェック
        $existingRating = Rating::where('purchase_id', $purchase->id)
            ->where('rater_id', $user->id)
            ->first();
            
        if ($existingRating) {
            return redirect()->route('items.index')
                ->with('error', '既にこの取引を評価しています。');
        }
        
        $validated = $request->validated();
        
        // 評価対象ユーザーを決定
        $ratedUserId = $isBuyer ? $purchase->item->user_id : $purchase->user_id;
        
        // 評価を作成
        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_id' => $user->id,
            'rated_user_id' => $ratedUserId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);
        
        // 評価フラグを更新
        if ($isBuyer) {
            $purchase->update(['buyer_evaluated' => true]);
        } else {
            $purchase->update(['seller_evaluated' => true]);
        }
        
        // 購入者が評価した場合、出品者にメール送信
        if ($isBuyer) {
            $this->sendCompletionEmail($purchase);
        }
        
        return redirect()->route('items.index')
            ->with('success', '評価を送信しました。');
    }

    /**
     * 取引完了メールを送信
     *
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    private function sendCompletionEmail(Purchase $purchase)
    {
        $seller = $purchase->item->user;
        
        try {
            Mail::to($seller->email)->send(new TradeCompletedMail($purchase));
        } catch (\Exception $e) {
            // メール送信エラーは記録するが、処理は続行
            \Log::error('取引完了メール送信エラー: ' . $e->getMessage());
        }
    }
}
