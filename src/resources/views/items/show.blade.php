<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $item->name }} - coachtech フリマ</title>
    <!-- CSSファイルは後でまとめて作成 -->
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
                    @else
                    <a href="/login" class="btn-login">ログイン</a>
                    <a href="/register" class="btn-register">会員登録</a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- メインコンテンツ -->
        <main class="main-content">
            <div class="item-detail">
                <!-- 商品画像 -->
                <div class="item-image">
                    <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                </div>

                <!-- 商品情報 -->
                <div class="item-info">
                    <h1 class="item-name">{{ $item->name }}</h1>

                    <!-- ブランド名 -->
                    @if($item->brand)
                    <p class="item-brand">{{ $item->brand }}</p>
                    @endif

                    <!-- 価格 -->
                    <div class="item-price">
                        <span class="price">¥{{ number_format($item->price) }}</span>
                        <span class="price-tax">(税込)</span>
                    </div>

                    <!-- いいね・コメント -->
                    <div class="item-stats">
                        <div class="stats-item">
                            @auth
                            <button class="like-btn {{ $isLiked ? 'liked' : '' }}"
                                data-item-id="{{ $item->id }}"
                                {{ $isOwnItem ? 'disabled' : '' }}>
                                <span class="like-icon">{{ $isLiked ? '★' : '☆' }}</span>
                            </button>
                            @else
                            <span class="like-icon">☆</span>
                            @endauth
                            <span class="like-count">{{ $likesCount }}</span>
                        </div>

                        <div class="stats-item">
                            <span class="comment-icon">💬</span>
                            <span class="comment-count">{{ $commentsCount }}</span>
                        </div>
                    </div>

                    <!-- 購入ボタン -->
                    @if($item->is_sold)
                    <div class="purchase-status">
                        <p class="sold-out">Sold Out</p>
                    </div>
                    @elseif(Auth::check() && $item->user_id === Auth::id())
                    <div class="own-item">
                        <p class="own-item-text">自分の商品です</p>
                    </div>
                    @else
                    @auth
                    <a href="{{ route('purchase.show', $item->id) }}" class="btn-purchase">
                        購入手続きへ
                    </a>
                    @else
                    <p class="purchase-login-required">
                        <a href="/login">ログイン</a>して購入
                    </p>
                    @endauth
                    @endif

                    <!-- 商品の説明 -->
                    <div class="item-description">
                        <h3>商品の説明</h3>
                        <p>{!! nl2br(e($item->description)) !!}</p>
                    </div>

                    <!-- 商品の情報 -->
                    <div class="item-details">
                        <h3>商品の情報</h3>
                        <table class="details-table">
                            <tr>
                                <td>カテゴリー</td>
                                <td>
                                    @foreach($item->categories as $category)
                                    <span class="category-tag">{{ $category->name }}</span>
                                    @if(!$loop->last), @endif
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td>商品の状態</td>
                                <td>{{ $item->condition->name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- コメント欄 -->
            <div class="comments-section">
                <h3>コメント ({{ $commentsCount }})</h3>

                <!-- コメント投稿フォーム -->
                @auth
                @if(!Auth::check() || $item->user_id !== Auth::id())
                <form class="comment-form" action="{{ route('items.comment', $item->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="comment">商品へのコメント</label>
                        <textarea
                            name="comment"
                            id="comment"
                            rows="4"
                            placeholder="コメントを入力してください"
                            required>{{ old('comment') }}</textarea>
                        @error('comment')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn-comment-submit">
                        コメントを送信する
                    </button>
                </form>
                @else
                <p class="comment-disabled">自分の商品にはコメントできません。</p>
                @endif
                @else
                <p class="comment-login-required">
                    <a href="/login">ログイン</a>してコメントを投稿
                </p>
                @endauth

                <!-- コメント一覧 -->
                <div class="comments-list">
                    @forelse($item->comments->sortByDesc('created_at') as $comment)
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-user">
                                @if($comment->user->profile && $comment->user->profile->profile_image)
                                <img src="{{ asset('storage/' . $comment->user->profile->profile_image) }}"
                                    alt="プロフィール画像" class="user-avatar">
                                @else
                                <div class="user-avatar-default">{{ mb_substr($comment->user->name, 0, 1) }}</div>
                                @endif
                                <span class="user-name">{{ $comment->user->name }}</span>
                            </div>
                            <span class="comment-date">{{ $comment->created_at->format('Y年m月d日 H:i') }}</span>
                        </div>
                        <div class="comment-content">
                            <p>{!! nl2br(e($comment->comment)) !!}</p>
                        </div>
                    </div>
                    @empty
                    <p class="no-comments">まだコメントがありません。</p>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // CSRF トークン設定
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // いいね機能
        const likeBtn = document.querySelector('.like-btn');
        if (likeBtn && !likeBtn.disabled) {
            likeBtn.addEventListener('click', function() {
                const itemId = this.dataset.itemId;

                fetch(`/items/${itemId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        // いいねボタンの状態更新
                        const likeIcon = this.querySelector('.like-icon');
                        const likeCount = document.querySelector('.like-count');

                        if (data.liked) {
                            this.classList.add('liked');
                            likeIcon.textContent = '★';
                        } else {
                            this.classList.remove('liked');
                            likeIcon.textContent = '☆';
                        }

                        likeCount.textContent = data.likesCount;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('エラーが発生しました。');
                    });
            });
        }

        // フラッシュメッセージの自動消去
        const flashMessage = document.querySelector('.flash-message');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.display = 'none';
            }, 3000);
        }
    </script>
</body>

</html>