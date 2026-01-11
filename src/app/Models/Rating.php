<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'rater_id',
        'rated_user_id',
        'rating',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
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
     * 評価者（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /**
     * 評価対象ユーザー（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /**
     * 評価を星マークで取得
     *
     * @return string
     */
    public function getStarRatingAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * 評価日を日本語形式で取得
     *
     * @return string
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y年m月d日');
    }

    /**
     * コメントがあるか
     *
     * @return bool
     */
    public function hasComment()
    {
        return !empty($this->comment);
    }

    /**
     * 特定ユーザーへの評価平均を計算
     *
     * @param  int  $userId
     * @return float|null
     */
    public static function averageRatingFor($userId)
    {
        $average = self::where('rated_user_id', $userId)->avg('rating');
        
        if ($average === null) {
            return null;
        }
        
        // 四捨五入
        return round($average);
    }

    /**
     * 特定ユーザーの評価数を取得
     *
     * @param  int  $userId
     * @return int
     */
    public static function countRatingsFor($userId)
    {
        return self::where('rated_user_id', $userId)->count();
    }

    /**
     * 特定ユーザーが受けた評価のスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('rated_user_id', $userId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 特定ユーザーが行った評価のスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRater($query, $userId)
    {
        return $query->where('rater_id', $userId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 高評価のスコープ（4以上）
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * 低評価のスコープ（2以下）
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowRated($query)
    {
        return $query->where('rating', '<=', 2);
    }
}
