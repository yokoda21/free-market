<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 既存のリレーション
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes', 'user_id', 'item_id')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchasedItems()
    {
        return $this->belongsToMany(Item::class, 'purchases', 'user_id', 'item_id')
            ->withPivot(['postal_code', 'address', 'building', 'payment_method'])
            ->withTimestamps();
    }

    /**
     * 送信した取引メッセージ（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentTradeMessages()
    {
        return $this->hasMany(TradeMessage::class, 'sender_id');
    }

    /**
     * 行った評価（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    /**
     * 受けた評価（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedRatings()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    /**
     * 評価平均を取得
     *
     * @return int|null
     */
    public function getAverageRatingAttribute()
    {
        return Rating::averageRatingFor($this->id);
    }

    /**
     * 評価数を取得
     *
     * @return int
     */
    public function getRatingCountAttribute()
    {
        return Rating::countRatingsFor($this->id);
    }

    /**
     * 取引中の購入を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activePurchases()
    {
        return $this->hasMany(Purchase::class)->inProgress();
    }

    /**
     * 取引中の出品を取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveListingsAttribute()
    {
        return Purchase::whereHas('item', function ($query) {
            $query->where('user_id', $this->id);
        })->inProgress()->get();
    }
}
