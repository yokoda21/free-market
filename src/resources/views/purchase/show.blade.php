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
    document.addEventListener('DOMContentLoaded', function() {
        const dropdown = document.querySelector('.custom-dropdown');
        const selected = document.getElementById('dropdown_selected');
        const selectedText = selected.querySelector('.selected-text');
        const options = document.getElementById('dropdown_options');
        const hiddenInput = document.getElementById('payment_method_hidden');
        const paymentDisplay = document.getElementById('payment-display');
        const allOptions = options.querySelectorAll('.dropdown-option');

        // 初期値の設定（old()値がある場合）
        const oldValue = hiddenInput.value;
        if (oldValue) {
            allOptions.forEach(option => {
                if (option.dataset.value === oldValue) {
                    option.classList.add('selected');
                    const text = option.querySelector('.option-text').textContent;
                    selectedText.textContent = text;
                    paymentDisplay.textContent = text;
                }
            });
        }

        // ドロップダウンの開閉
        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        // オプション選択
        allOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();

                // 以前の選択をクリア
                allOptions.forEach(opt => opt.classList.remove('selected'));

                // 新しい選択を設定
                this.classList.add('selected');
                const value = this.dataset.value;
                const text = this.querySelector('.option-text').textContent;

                // 値を更新
                hiddenInput.value = value;
                selectedText.textContent = text;
                paymentDisplay.textContent = text;

                // ドロップダウンを閉じる
                dropdown.classList.remove('active');
            });
        });

        // 外側をクリックしたら閉じる
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
</script>
@endsection