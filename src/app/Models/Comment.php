<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
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
        'comment',
    ];

    /**
     * ユーザー（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品（多対1）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * コメントの日付を日本語形式で取得
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y年m月d日 H:i');
    }

    /**
     * コメントの相対時間を取得（例：2時間前）
     *
     * @return string
     */
    public function getRelativeTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * コメント文字数を取得
     *
     * @return int
     */
    public function getCommentLengthAttribute()
    {
        return mb_strlen($this->comment);
    }

    /**
     * 最新順でコメントを取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 古い順でコメントを取得するスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
