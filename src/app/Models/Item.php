<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'brand',
        'condition_id',
        'image_url',
        'is_sold',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_sold' => 'boolean',
        'price' => 'integer',
    ];

    /**
     * 出品者（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品状態（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * 商品カテゴリ中間テーブル（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    /**
     * カテゴリ（多対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_categories', 'item_id', 'category_id')
                    ->withTimestamps();
    }

    /**
     * いいね（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * いいねしたユーザー（多対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'item_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * コメント（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 購入情報（1対0または1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    /**
     * 購入者（多対多、実際は1対0または1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchasedUsers()
    {
        return $this->belongsToMany(User::class, 'purchases', 'item_id', 'user_id')
                    ->withPivot(['postal_code', 'address', 'building', 'payment_method'])
                    ->withTimestamps();
    }

    /**
     * 商品画像のフルURLを取得
     *
     * @return string|null
     */
    public function getImageUrlFullAttribute()
    {
        if ($this->image_url) {
            return asset('storage/' . $this->image_url);
        }
        return null;
    }

    /**
     * 価格を円形式で取得
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return '¥' . number_format($this->price);
    }

    /**
     * 販売状況のテキストを取得
     *
     * @return string
     */
    public function getSaleStatusAttribute()
    {
        return $this->is_sold ? '売り切れ' : '販売中';
    }

    /**
     * いいね数を取得
     *
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * コメント数を取得
     *
     * @return int
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * 指定ユーザーがこの商品をいいねしているか確認
     *
     * @param  int  $userId
     * @return bool
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * この商品を購入したユーザーを取得
     *
     * @return \App\Models\User|null
     */
    public function getPurchaser()
    {
        return $this->purchase ? $this->purchase->user : null;
    }

    /**
     * 販売中の商品のみ取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_sold', false);
    }

    /**
     * 売り切れ商品のみ取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSold($query)
    {
        return $query->where('is_sold', true);
    }

    /**
     * 価格範囲で絞り込むスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $minPrice
     * @param  int  $maxPrice
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        
        return $query;
    }

    /**
     * 取引中か確認
     *
     * @return bool
     */
    public function isInTrade()
    {
        return $this->purchase && !$this->purchase->is_completed;
    }

    /**
     * 取引完了済みか確認
     *
     * @return bool
     */
    public function isTradeCompleted()
    {
        return $this->purchase && $this->purchase->is_completed;
    }

    /**
     * 指定ユーザーがこの商品を購入したか確認
     *
     * @param  int  $userId
     * @return bool
     */
    public function isPurchasedBy($userId)
    {
        return $this->purchase && $this->purchase->user_id === $userId;
    }
}
