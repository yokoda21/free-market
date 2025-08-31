<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>配送先住所変更 - coachtech フリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
</head>

<body>
    <div class="container">
        <!-- ヘッダー -->
        <header class="header">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="{{ asset('images/logo.svg') }}" alt="coachtech">
                </a>

                <div class="header-actions">
                    @auth
                    <a href="{{ route('items.create') }}" class="btn-sell">出品</a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout">ログアウト</button>
                    </form>
                    <a href="/mypage" class="btn-mypage">マイページ</a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- メインコンテンツ -->
        <main class="main-content">
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

            <div class="address-container">
                <div class="address-content">
                    <!-- ページタイトル -->
                    <div class="page-header">
                        <h1>配送先住所の変更</h1>
                        <p class="page-description">
                            配送先住所を変更します。入力後、「更新する」ボタンを押してください。
                        </p>
                    </div>

                    <!-- 住所変更フォーム -->
                    <form class="address-form" action="{{ route('purchase.address.update', $item->id) }}" method="POST">
                        @csrf

                        <!-- 郵便番号 -->
                        <div class="form-group">
                            <label for="postal_code" class="required">郵便番号</label>
                            <input
                                type="text"
                                name="postal_code"
                                id="postal_code"
                                placeholder="123-4567"
                                value="{{ old('postal_code', $profile->postal_code ?? '') }}"
                                required>
                            <span class="input-note">ハイフン（-）を含めて入力してください</span>
                            @error('postal_code')
                            <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 住所 -->
                        <div class="form-group">
                            <label for="address" class="required">住所</label>
                            <input
                                type="text"
                                name="address"
                                id="address"
                                placeholder="東京都渋谷区千駄ヶ谷1-2-3"
                                value="{{ old('address', $profile->address ?? '') }}"
                                required>
                            <span class="input-note">都道府県から番地まで入力してください</span>
                            @error('address')
                            <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 建物名 -->
                        <div class="form-group">
                            <label for="building">建物名</label>
                            <input
                                type="text"
                                name="building"
                                id="building"
                                placeholder="千駄ヶ谷マンション101"
                                value="{{ old('building', $profile->building ?? '') }}">
                            <span class="input-note">マンション名、部屋番号など（任意）</span>
                            @error('building')
                            <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 現在の住所表示（参考） -->
                        @if($profile && $profile->address)
                        <div class="current-address">
                            <h3>現在の住所</h3>
                            <div class="address-display">
                                <p>{{ $profile->postal_code }}</p>
                                <p>{{ $profile->address }}</p>
                                @if($profile->building)
                                <p>{{ $profile->building }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- アクションボタン -->
                        <div class="form-actions">
                            <a href="{{ route('purchase.show', $item->id) }}" class="btn-cancel">
                                キャンセル
                            </a>
                            <button type="submit" class="btn-update">
                                更新する
                            </button>
                        </div>
                    </form>

                    <!-- 購入予定商品情報 -->
                    <div class="item-info">
                        <h3>購入予定商品</h3>
                        <div class="item-summary">
                            <div class="item-image">
                                <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                            </div>
                            <div class="item-details">
                                <h4 class="item-name">{{ $item->name }}</h4>
                                <div class="item-price">¥{{ number_format($item->price) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // 郵便番号の自動フォーマット
        const postalCodeInput = document.getElementById('postal_code');
        if (postalCodeInput) {
            postalCodeInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, ''); // 数字のみ
                if (value.length >= 4) {
                    value = value.slice(0, 3) + '-' + value.slice(3, 7);
                }
                e.target.value = value;
            });
        }

        // フラッシュメッセージの自動消去
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });

        // フォーム送信時の確認
        const addressForm = document.querySelector('.address-form');
        if (addressForm) {
            addressForm.addEventListener('submit', function(e) {
                const confirmed = confirm('住所を更新しますか？');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        }

        // 入力フィールドのリアルタイム検証
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#ff4444';
                } else {
                    this.style.borderColor = '#ddd';
                }
            });
        });
    </script>