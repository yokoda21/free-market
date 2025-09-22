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
                <select name="payment_method" id="payment_method" required>
                    <option value="">選択してください</option>
                    @foreach($paymentMethods as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- 配送先 -->
            <div class="shipping-info">
                <div class="shipping-header">
                    <h3>配送先</h3>
                    <a href="{{ route('purchase.address', $item->id) }}" class="change-link">変更する</a>
                </div>
                <div class="address-info">
                    〒{{ $profile->postal_code ?? '123-4567' }}<br>
                    {{ $profile->address ?? '東京都渋谷区' }}<br>
                    {{ $profile->building ?? 'マンション101' }}
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

<script>
    // 支払い方法の表示更新
    const paymentSelect = document.getElementById('payment_method');
    const paymentDisplay = document.getElementById('payment-display');

    if (paymentSelect && paymentDisplay) {
        paymentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            paymentDisplay.textContent = selectedOption.text === '選択してください' ? '未選択' : selectedOption.text;
        });
    }
</script>
@endsection