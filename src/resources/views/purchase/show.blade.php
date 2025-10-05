@extends('layouts.app')

@section('title', '商品購入 - ' . $item->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<div class="purchase-container">
    <form class="purchase-form" action="{{ route('purchase.store', $item->id) }}" method="POST">
        @csrf

        <!-- 左側：商品情報 -->
        <div class="left-section">
            <!-- 商品カード -->
            <div class="item-card">
                <div class="item-image">
                    <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                </div>
                <div class="item-info">
                    <h2 class="item-name">{{ $item->name }}</h2>
                    <div class="item-price">¥{{ number_format($item->price) }}</div>
                </div>
            </div>

            <!-- 支払い方法 -->
            <div class="payment-method">
                <h3>支払い方法</h3>
                <!-- カスタムセレクトボックス -->
                <div class="custom-dropdown @error('payment_method') error @enderror">
                    <input type="hidden" name="payment_method" id="payment_method_hidden" value="{{ old('payment_method', '') }}">
                    <div class="dropdown-selected" id="dropdown_selected">
                        <span class="selected-text">選択してください</span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="dropdown-options" id="dropdown_options">
                        @foreach($paymentMethods as $value => $label)
                        <div class="dropdown-option" data-value="{{ $value }}">
                            <span class="option-check">✓</span>
                            <span class="option-text">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @error('payment_method')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- 配送先 -->
            <div class="shipping-info">
                <div class="shipping-header">
                    <h3>配送先</h3>
                    <a href="{{ route('purchase.address', $item->id) }}" class="change-link">変更する</a>
                </div>
                <div class="address-info">
                    <div class="address-line">〒{{ $profile->postal_code ?? '123-4567' }}</div>
                    <div class="address-line">{{ $profile->address ?? '東京都渋谷区' }}</div>
                    <div class="address-line">{{ $profile->building ?? 'マンション101' }}</div>
                </div>
            </div>
        </div>

        <!-- 右側：購入確認 -->
        <div class="right-section">
            <div class="summary-box">
                <div class="summary-item">
                    <span>商品代金</span>
                    <span>¥{{ number_format($item->price) }}</span>
                </div>
                <div class="summary-item">
                    <span>支払い方法</span>
                    <span id="payment-display">未選択</span>
                </div>
            </div>

            <button type="submit" class="purchase-btn">購入する</button>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('js/purchase.js') }}"></script>
@endpush

@endsection