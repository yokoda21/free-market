@extends('layouts.app')

@section('title', '取引中の商品')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/trades.css') }}">
@endpush

@section('content')
<div class="trades-container">
    <h1 class="trades-title">取引中の商品</h1>

    <!-- タブナビゲーション -->
    <div class="trades-tabs">
        <button class="trades-tab active" data-tab="purchases">購入した商品</button>
        <button class="trades-tab" data-tab="sales">出品した商品</button>
    </div>

    <!-- 購入した商品の取引一覧 -->
    <div class="trades-content" id="purchases-content">
        @if($purchases->count() > 0)
        <div class="trades-list">
            @foreach($purchases as $purchase)
            <a href="{{ route('trades.show', $purchase->id) }}" class="trade-item">
                <div class="trade-item__image">
                    @if($purchase->item->image_url)
                        @if(str_starts_with($purchase->item->image_url, 'http'))
                        <img src="{{ $purchase->item->image_url }}" alt="{{ $purchase->item->name }}">
                        @else
                        <img src="{{ asset('storage/' . $purchase->item->image_url) }}" alt="{{ $purchase->item->name }}">
                        @endif
                    @else
                    <div class="no-image">画像なし</div>
                    @endif
                    
                    @php
                        $unreadCount = $purchase->getUnreadCountFor(Auth::id());
                    @endphp
                    @if($unreadCount > 0)
                    <div class="unread-badge">{{ $unreadCount }}</div>
                    @endif
                </div>
                <div class="trade-item__info">
                    <h3 class="trade-item__name">{{ $purchase->item->name }}</h3>
                    <p class="trade-item__seller">出品者: {{ $purchase->item->user->name }}</p>
                    @if($purchase->latest_message)
                    <p class="trade-item__message">{{ Str::limit($purchase->latest_message->message, 50) }}</p>
                    <p class="trade-item__time">{{ $purchase->latest_message->created_at->format('Y/m/d H:i') }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <p>取引中の購入商品はありません</p>
        </div>
        @endif
    </div>

    <!-- 出品した商品の取引一覧 -->
    <div class="trades-content" id="sales-content" style="display: none;">
        @if($sales->count() > 0)
        <div class="trades-list">
            @foreach($sales as $sale)
            <a href="{{ route('trades.show', $sale->id) }}" class="trade-item">
                <div class="trade-item__image">
                    @if($sale->item->image_url)
                        @if(str_starts_with($sale->item->image_url, 'http'))
                        <img src="{{ $sale->item->image_url }}" alt="{{ $sale->item->name }}">
                        @else
                        <img src="{{ asset('storage/' . $sale->item->image_url) }}" alt="{{ $sale->item->name }}">
                        @endif
                    @else
                    <div class="no-image">画像なし</div>
                    @endif
                    
                    @php
                        $unreadCount = $sale->getUnreadCountFor(Auth::id());
                    @endphp
                    @if($unreadCount > 0)
                    <div class="unread-badge">{{ $unreadCount }}</div>
                    @endif
                </div>
                <div class="trade-item__info">
                    <h3 class="trade-item__name">{{ $sale->item->name }}</h3>
                    <p class="trade-item__seller">購入者: {{ $sale->user->name }}</p>
                    @if($sale->latest_message)
                    <p class="trade-item__message">{{ Str::limit($sale->latest_message->message, 50) }}</p>
                    <p class="trade-item__time">{{ $sale->latest_message->created_at->format('Y/m/d H:i') }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <p>取引中の出品商品はありません</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.trades-tab');
    const contents = {
        'purchases': document.getElementById('purchases-content'),
        'sales': document.getElementById('sales-content')
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // タブの切り替え
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // コンテンツの切り替え
            Object.keys(contents).forEach(key => {
                if (key === targetTab) {
                    contents[key].style.display = 'block';
                } else {
                    contents[key].style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush
@endsection
