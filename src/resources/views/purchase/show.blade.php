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
                <select
                    name="payment_method"
                    id="payment_method"
                    class="@error('payment_method') error @enderror"
                    aria-required="true">
                    <option value="" id="placeholder-option">選択してください</option>
                    @foreach($paymentMethods as $value => $label)
                    <option value="{{ $value }}" data-label="{{ $label }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                        {{ old('payment_method') == $value ? '✓ ' : '' }}{{ $label }}
                    </option>
                    @endforeach
                </select>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentSelect = document.getElementById('payment_method');
        const placeholderOption = document.getElementById('placeholder-option');

        // 選択が変更されたとき
        paymentSelect.addEventListener('change', function() {
            // すべてのオプションからチェックマークを削除
            Array.from(paymentSelect.options).forEach(option => {
                if (option.value !== '') {
                    const originalLabel = option.getAttribute('data-label');
                    option.textContent = originalLabel;
                }
            });

            // 選択されたオプションにチェックマークを追加
            if (this.value !== '') {
                const selectedOption = this.options[this.selectedIndex];
                const originalLabel = selectedOption.getAttribute('data-label');
                selectedOption.textContent = '✓ ' + originalLabel;

                // 「選択してください」を削除
                if (placeholderOption) {
                    placeholderOption.remove();
                }
            }
        });

        // ページ読み込み時に既に値が選択されている場合
        if (paymentSelect.value !== '') {
            const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
            const originalLabel = selectedOption.getAttribute('data-label');
            selectedOption.textContent = '✓ ' + originalLabel;

            if (placeholderOption) {
                placeholderOption.remove();
            }
        }
    });
</script>