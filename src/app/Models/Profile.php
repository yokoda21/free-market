<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'postal_code',
        'address',
        'building',
        'profile_image',
    ];

    /**
     * ユーザー（1対1の逆）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * プロフィール画像のフルURLを取得
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }

    /**
     * 住所の完全版を取得（郵便番号 + 住所 + 建物名）
     *
     * @return string
     */
    public function getFullAddressAttribute()
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
}
