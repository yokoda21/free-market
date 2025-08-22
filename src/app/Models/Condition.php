<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * この状態の商品（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * この状態の商品数を取得
     *
     * @return int
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    /**
     * この状態の販売中商品数を取得
     *
     * @return int
     */
    public function getAvailableItemsCountAttribute()
    {
        return $this->items()->where('is_sold', false)->count();
    }
}
