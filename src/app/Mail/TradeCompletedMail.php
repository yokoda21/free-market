<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchase;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('【coachtechフリマ】取引が完了しました')
                    ->view('emails.trade_completed')
                    ->with([
                        'itemName' => $this->purchase->item->name,
                        'buyerName' => $this->purchase->user->name,
                        'completedAt' => $this->purchase->completed_at->format('Y年m月d日 H:i'),
                    ]);
    }
}
