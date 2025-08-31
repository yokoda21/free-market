<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>商品購入 - {{ $item->name }} - coachtech フリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
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

            <div class="purchase-container">
                <div class="purchase-content">
                    <!-- 商品情報セクション -->
                    <div class="item-summary">
                        <div class="item-image">
                            <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                        </div>
                        <div class="item-details">
                            <h2 class="item-name">{{ $item->name }}</h2>
                            <div class="item-price">¥{{ number_format($item->price) }}</div>
                        </div>
                    </div>

                    <!-- 購入フォーム -->
                    <form class="purchase-form" action="{{ route('purchase.store', $item->id) }}" method="POST">
                        @csrf

                        <!-- 支払い方法選択（FN023） -->
                        <div class="form-section">
                            <h3>支払い方法</h3>
                            <div class="form-group">
                                <select name="payment_method" id="payment_method" required>
                                    <option value="">選択してください</option>
                                    @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- 配送先情報（FN024） -->
                        <div class="form-section">
                            <div class="section-header">
                                <h3>配送先</h3>
                                <a href="{{ route('purchase.address', $item->id) }}" class="change-address-link">
                                    配送先を変更する
                                </a>
                            </div>

                            <!-- 郵便番号 -->
                            <div class="form-group">
                                <label for="postal_code">郵便番号</label>
                                <input
                                    type="text"
                                    name="postal_code"
                                    id="postal_code"
                                    placeholder="123-4567"
                                    value="{{ old('postal_code', $profile->postal_code ?? '') }}"
                                    required>
                                @error('postal_code')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 住所 -->
                            <div class="form-group">
                                <label for="address">住所</label>
                                <input
                                    type="text"
                                    name="address"
                                    id="address"
                                    placeholder="東京都渋谷区千駄ヶ谷1-2-3"
                                    value="{{ old('address', $profile->address ?? '') }}"
                                    required>
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
                                    placeholder="千駄ヶ谷マンション101（任意）"
                                    value="{{ old('building', $profile->building ?? '') }}">
                                @error('building')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- 購入確認セクション -->
                        <div class="form-section">
                            <div class="purchase-summary">
                                <div class="summary-item">
                                    <span class="summary-label">商品代金</span>
                                    <span class="summary-value">¥{{ number_format($item->price) }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">支払い方法</span>
                                    <span class="summary-value" id="payment-display">未選択</span>
                                </div>
                                <div class="summary-total">
                                    <span class="total-label">支払い金額</span>
                                    <span class="total-value">¥{{ number_format($item->price) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 購入ボタン -->
                        <div class="form-actions">
                            <button type="submit" class="btn-purchase">
                                購入する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // 支払い方法の表示更新（FN023要件）
        const paymentSelect = document.getElementById('payment_method');
        const paymentDisplay = document.getElementById('payment-display');

        if (paymentSelect && paymentDisplay) {
            paymentSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                paymentDisplay.textContent = selectedOption.text === '選択してください' ? '未選択' : selectedOption.text;
            });

            // 初期表示設定
            const currentValue = paymentSelect.value;
            if (currentValue) {
                const currentOption = paymentSelect.querySelector(`option[value="${currentValue}"]`);
                if (currentOption) {
                    paymentDisplay.textContent = currentOption.text;
                }
            }
        }

        // フラッシュメッセージの自動消去
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });

        // フォーム送信時の確認
        const purchaseForm = document.querySelector('.purchase-form');
        if (purchaseForm) {
            purchaseForm.addEventListener('submit', function(e) {
                const paymentMethod = document.getElementById('payment_method').value;

                if (!paymentMethod) {
                    e.preventDefault();
                    alert('支払い方法を選択してください。');
                    return;
                }

                const confirmed = confirm('この内容で購入しますか？');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        }
    </script>

    