@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
<div class="profile-container">
    <!-- プロフィールヘッダー -->
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-image">
                @if($user->profile && $user->profile->profile_image)
                <img src="{{ Storage::url($user->profile->profile_image) }}" alt="プロフィール画像" class="profile-img">
                @else
                <div class="profile-img-placeholder">
                    <i class="icon-user"></i>
                </div>
                @endif
            </div>
            <h1 class="profile-name">{{ $user->name }}</h1>
            <a href="{{ route('user.profile.edit') }}" class="edit-profile-btn">プロフィールを編集</a>
        </div>
    </div>

    <!-- タブナビゲーション -->
    <div class="tab-navigation">
        <div class="tab-buttons">
            <a href="{{ route('user.profile', ['tab' => 'sell']) }}"
                class="tab-button {{ $tab === 'sell' ? 'active' : '' }}">
                出品した商品
            </a>
            <a href="{{ route('user.profile', ['tab' => 'buy']) }}"
                class="tab-button {{ $tab === 'buy' ? 'active' : '' }}">
                購入した商品
            </a>
        </div>
    </div>

    <!-- 商品一覧表示 -->
    <div class="items-grid">
        @if($items->count() > 0)
        @foreach($items as $item)
        <div class="item-card">
            <a href="{{ route('items.show', $item->id) }}" class="item-link">
                <div class="item-image">
                    @if($item->image_url)
                    <img src="{{ Storage::url($item->image_url) }}" alt="{{ $item->name }}">
                    @else
                    <div class="no-image">画像なし</div>
                    @endif

                    @if($item->is_sold)
                    <div class="sold-label">Sold</div>
                    @endif
                </div>

                <div class="item-info">
                    <h3 class="item-name">{{ $item->name }}</h3>
                    <p class="item-price">¥{{ number_format($item->price) }}</p>

                    @if($tab === 'buy' && $item->purchase)
                    <p class="purchase-date">購入日: {{ $item->purchase->created_at->format('Y年m月d日') }}</p>
                    <p class="purchase-address">
                        配送先: {{ $item->purchase->postal_code }} {{ $item->purchase->address }}
                        @if($item->purchase->building)
                        {{ $item->purchase->building }}
                        @endif
                    </p>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
        @else
        <div class="no-items">
            @if($tab === 'sell')
            <p>出品した商品はありません</p>
            <a href="{{ route('items.create') }}" class="sell-btn">商品を出品する</a>
            @else
            <p>購入した商品はありません</p>
            <a href="{{ route('items.index') }}" class="browse-btn">商品を探す</a>
            @endif
        </div>
        @endif
    </div>
</div>

