@extends('layouts.auth')

@section('title', 'ログイン')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth__content">
    <div class="auth-form__heading">
        <h2>ログイン</h2>
    </div>
    <form class="form" action="{{ route('login.custom') }}" method="post" novalidate>
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">メールアドレス</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">パスワード</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="password" name="password">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">ログインする</button>
        </div>
    </form>
    <div class="login__link">
        <a class="login__button-submit" href="{{ route('register') }}">会員登録はこちら</a>
    </div>
</div>
@endsection