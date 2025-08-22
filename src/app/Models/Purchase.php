<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'postal_code',
        'address',
        'building',
        'payment_method',
    ];

    /**
     * 購入者（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 購入した商品（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 配送先住所の完全版を取得
     *
     * @return string
     */
    public function getFullShippingAddressAttribute()
    {
        $address = '';
        
        if ($this->postal_code) {
            $address .= '〒' . $this->postal_code . ' ';
        }
        
        if ($this->address) {
            $address .= $this->address;
        }
        
        if ($this->building) {
            $address .= ' ' . $this->building;
        }
        
        return trim($address);
    }

    /**
     * 購入日を日本語形式で取得
     *
     * @return string
     */
    public function getFormattedPurchaseDateAttribute()
    {
        return $this->created_at->format('Y年m月d日 H:i');
    }

    /**
     * 支払い方法の表示名を取得
     *
     * @return string
     */
    public function getPaymentMethodDisplayAttribute()
    {
        $methods = [
            'convenience' => 'コンビニ支払い',
            'card' => 'カード支払い',
            'bank_transfer' => '銀行振込',
            // 必要に応じて追加
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * 購入価格を取得（商品価格）
     *
     * @return int|null
     */
    public function getPurchasePriceAttribute()
    {
        return $this->item ? $this->item->price : null;
    }

    /**
     * 購入価格を円形式で取得
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        $price = $this->getPurchasePriceAttribute();
        return $price ? '¥' . number_format($price) : '価格不明';
    }

    /**
     * 最新の購入順で取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 指定期間の購入を取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 支払い方法で絞り込むスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
