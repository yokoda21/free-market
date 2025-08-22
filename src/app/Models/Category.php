<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Category extends Model
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
     * 商品カテゴリ中間テーブル（1対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    /**
     * このカテゴリに属する商品（多対多）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_categories', 'category_id', 'item_id')
                    ->withTimestamps();
    }

    /**
     * カテゴリに属する商品数を取得
     *
     * @return int
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    /**
     * カテゴリに属する販売中の商品数を取得
     *
     * @return int
     */
    public function getAvailableItemsCountAttribute()
    {
        return $this->items()->where('is_sold', false)->count();
    }
}
