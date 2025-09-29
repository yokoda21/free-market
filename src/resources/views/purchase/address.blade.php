@extends('layouts.app')

@section('title', '住所の変更')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/address-edit.css') }}">
@endpush

@section('content')
<!-- フラッシュメッセージ -->
@if (session('success'))
<div class="flash-message success">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="flash-message error">
    {{ session('error') }}
</div>
@endif

<div class="address-edit-container">
    <div class="address-edit-header">
        <h1>住所の変更</h1>
    </div>

    <form class="address-edit-form" action="{{ route('purchase.address.update', request()->route('item_id')) }}" method="POST">
        @csrf

        <!-- 郵便番号 -->
        <div class="form-group">
            <label for="postal_code" class="form-label">郵便番号</label>
            <input
                type="text"
                id="postal_code"
                name="postal_code"
                class="form-input @error('postal_code') error @enderror"
                value="{{ old('postal_code', $profile->postal_code ?? '') }}"
                aria-required="true">
            @error('postal_code')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 住所 -->
        <div class="form-group">
            <label for="address" class="form-label">住所</label>
            <input
                type="text"
                id="address"
                name="address"
                class="form-input @error('address') error @enderror"
                value="{{ old('address', $profile->address ?? '') }}"
                aria-required="true">
            @error('address')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 建物名 -->
        <div class="form-group">
            <label for="building" class="form-label">建物名</label>
            <input
                type="text"
                id="building"
                name="building"
                class="form-input @error('building') error @enderror"
                value="{{ old('building', $profile->building ?? '') }}">
            @error('building')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 更新ボタン -->
        <div class="form-actions">
            <button type="submit" class="btn-update">更新する</button>
        </div>
    </form>
</div>

<script>
    // 郵便番号フォーマット（自動ハイフン挿入）
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value.length >= 4) {
            value = value.slice(0, 3) + '-' + value.slice(3, 7);
        }
        e.target.value = value;
    });

    // フラッシュメッセージの自動消去
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000);
    });
</script>
@endsection