@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')
<div class="profile-container">
    <!-- プロフィールヘッダー -->
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-image">
                @if($user->profile && $user->profile->profile_image)
                <img src="{{ asset('storage/' . $user->profile->profile_image) }}" alt="プロフィール画像" class="profile-img">
                @else
                <div class="profile-img-placeholder"></div>
                @endif
            </div>
            <div class="profile-user">
                <h1 class="profile-name">{{ $user->name }}</h1>
                @if($user->average_rating)
                <div class="profile-rating">
                    <div class="profile-rating__stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <=$user->average_rating)
                            <span class="star star--filled">★</span>
                            @else
                            <span class="star">★</span>
                            @endif
                            @endfor
                    </div>
                    <span class="profile-rating__count">({{ $user->rating_count }})</span>
                </div>
            </div>
            @endif
            <a href="{{ route('user.edit-profile') }}" class="edit-profile-btn">プロフィールを編集</a>
        </div>
    </div>

    <!-- タブナビゲーション -->
    <div class="tab-navigation">
        <div class="tab-buttons">
            <a href="{{ route('user.profile', ['page' => 'sell']) }}"
                class="tab-button {{ $page === 'sell' ? 'active' : '' }}">
                出品した商品
            </a>
            <a href="{{ route('user.profile', ['page' => 'buy']) }}"
                class="tab-button {{ $page === 'buy' ? 'active' : '' }}">
                購入した商品
            </a>
            <a href="{{ route('user.profile', ['page' => 'trading']) }}"
                class="tab-button {{ $page === 'trading' ? 'active' : '' }}">
                取引中の商品
                @php
                // 購入した取引の未読メッセージ数
                $purchasedUnreadCount = 0;
                $purchasedTrades = \App\Models\Purchase::where('user_id', Auth::id())
                ->where(function($q) {
                $q->where('is_completed', false)
                ->orWhere(function($q2) {
                $q2->where('is_completed', true)
                ->where('buyer_evaluated', false);
                });
                })
                ->get();
                foreach($purchasedTrades as $trade) {
                $purchasedUnreadCount += $trade->getUnreadCountFor(Auth::id());
                }

                // 出品した取引の未読メッセージ数
                $soldUnreadCount = 0;
                $soldTrades = \App\Models\Purchase::whereHas('item', function($q) {
                $q->where('user_id', Auth::id())
                ->where('is_sold', true);
                })
                ->where(function($q) {
                $q->where('is_completed', false)
                ->orWhere(function($q2) {
                $q2->where('is_completed', true)
                ->where('seller_evaluated', false);
                });
                })
                ->get();
                foreach($soldTrades as $trade) {
                $soldUnreadCount += $trade->getUnreadCountFor(Auth::id());
                }

                $unreadCount = $purchasedUnreadCount + $soldUnreadCount;
                @endphp
                @if($unreadCount > 0)
                <span class="tab-badge">{{ $unreadCount }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- 商品一覧表示 -->
    <div class="items-grid">
        @if($items->count() > 0)
        @foreach($items as $item)
        <div class="item-card">
            @if($page === 'trading' && $item->purchase)
            @php
            $unreadCount = $item->purchase->getUnreadCountFor(Auth::id());
            @endphp
            @if($unreadCount > 0)
            <div class="unread-badge">{{ $unreadCount }}</div>
            @endif
            @endif
            <a href="{{ ($page === 'trading' && $item->purchase) ? route('trades.show', $item->purchase->id) : route('items.show', $item->id) }}" class="item-link">
                <div class="item-image">
                    @if($item->image_url)
                    @if(str_starts_with($item->image_url, 'http'))
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                    @else
                    <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                    @endif
                    @else
                    <div class="no-image">画像なし</div>
                    @endif

                    @if($item->is_sold)
                    <div class="sold-label">Sold</div>
                    @endif

                </div>


                <div class="item-info">
                    <h3 class="item-name">{{ $item->name }}</h3>
                </div>
            </a>
        </div>
        @endforeach
        @else
        <div class="no-items">
            @if($page === 'sell')
            <p>出品した商品はありません</p>
            <a href="{{ route('items.create') }}" class="sell-btn">商品を出品する</a>
            @elseif($page === 'buy')
            <p>購入した商品はありません</p>
            <a href="{{ route('items.index') }}" class="browse-btn">商品を探す</a>
            @elseif($page === 'trading')
            <p>取引中の商品はありません</p>
            <p class="no-items-hint">商品が購入されるとここに表示されます</p>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection