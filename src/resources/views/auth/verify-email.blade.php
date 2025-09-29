@extends('layouts.auth')

@section('title', 'メール認証')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth__content">
    <div class="verification__content">
        @if (session('status'))
        <div class="form__success">
            {{ session('status') }}
        </div>
        @endif

        @if (session('message'))
        <div class="form__info">
            {{ session('message') }}
        </div>
        @endif

        <div class="verification__message">
            <p class="message-line1">登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p class="message-line2">メール認証を完了してください。</p>
        </div>

        <div class="verification__actions">
            <div class="verification__button">
                <button type="button" class="form__button-gray">認証はこちらから</button>
            </div>

            <div class="verification__resend">
                <form method="POST" action="{{ route('verification.send') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="resend__link">認証メールを再送する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .verification__content {
        text-align: center;
        max-width: 720px;
        height: 58px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .verification__message {
        font-family: Inter;
        font-weight: 700;
        font-style: Bold;
        font-size: 24px;
        leading-trim: NONE;
        line-height: 100%;
        letter-spacing: 0%;
        text-align: center;
        width: 720px;
        height: 58px;

    }

    .verification__message p {
        margin: 8px 0;
    }

    .verification__actions {
        margin: 60px 0;
    }

    .verification__button {
        margin-bottom: 40px;
        margin-left: 80px;
    }

    .form__button-gray {
        background-color: #D9D9D9;
        border: 1px solid #000000;
        padding: 15px 40px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 24px;
        font-weight: 700;
        font-style: bold;
        min-width: 200px;
        text-align: center;

    }

    .form__button-gray:hover {
        background-color: #666;
    }

    .verification__resend {
        margin-top: 30px;
    }

    .resend__link {
        background: none;
        border: none;
        color: #007bff;
        text-decoration: underline;
        cursor: pointer;
        font-size: 20px;
        padding: 0;
        margin-left: 80px;
    }

    .resend__link:hover {
        color: #0056b3;
    }

    .form__success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .form__info {
        background-color: #cce7ff;
        color: #0c5460;
        border: 1px solid #b0daff;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>
@endsection