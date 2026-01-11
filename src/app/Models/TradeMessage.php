<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'sender_id',
        'message',
        'image_path',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * 購入情報（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * 送信者（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * メッセージを既読にする
     *
     * @return bool
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            return $this->update(['is_read' => true]);
        }
        return true;
    }

    /**
     * 送信日時を日本語形式で取得
     *
     * @return string
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y年m月d日 H:i');
    }

    /**
     * 画像が添付されているか
     *
     * @return bool
     */
    public function hasImage()
    {
        return !empty($this->image_path);
    }

    /**
     * 画像URLを取得
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if ($this->hasImage()) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * 未読メッセージのスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * 特定の購入に関するメッセージのスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $purchaseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPurchase($query, $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId)
            ->orderBy('created_at', 'asc');
    }

    /**
     * 特定のユーザーが送信したメッセージのスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySender($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }
}
