<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
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
     * 指定のユーザーと商品の組み合わせが存在するかチェック
     *
     * @param  int  $userId
     * @param  int  $itemId
     * @return bool
     */
    public static function exists($userId, $itemId)
    {
        return static::where('user_id', $userId)
                     ->where('item_id', $itemId)
                     ->exists();
    }

    /**
     * いいねを切り替え（トグル）
     *
     * @param  int  $userId
     * @param  int  $itemId
     * @return array ['action' => 'added'|'removed', 'like' => Like|null]
     */
    public static function toggle($userId, $itemId)
    {
        $like = static::where('user_id', $userId)
                     ->where('item_id', $itemId)
                     ->first();

        if ($like) {
            $like->delete();
            return ['action' => 'removed', 'like' => null];
        } else {
            $newLike = static::create([
                'user_id' => $userId,
                'item_id' => $itemId,
            ]);
            return ['action' => 'added', 'like' => $newLike];
        }
    }
}
