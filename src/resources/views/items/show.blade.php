@extends('layouts.app')

@section('title', $item->name . ' - 商品詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')
<main class="detail-main">
    <div class="detail-container">
        <!-- 商品詳細コンテンツ -->
        <div class="item-detail">
            <!-- 商品画像 (左側に表示）-->
            <div class="item-image">
                <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}" class="item-image__img">
            </div>

            <!-- 商品情報 （右側に表示）-->
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
                <div class="purchase-section">
                    @if($item->is_sold)
                    <div class="purchase-status">
                        <div class="sold-out">Sold Out</div>
                    </div>
                    @elseif(Auth::check() && $item->user_id === Auth::id())
                    <div class="own-item">
                        <div class="own-item-text">自分の商品です</div>
                    </div>
                    @elseif(Auth::check())
                    <a href="{{ route('purchase.show', $item->id) }}" class="btn-purchase">
                        購入手続きへ
                    </a>
                    @else
                    <a href="/login" class="btn-purchase-login">
                        購入手続きへ
                    </a>
                    @endif
                </div>

                <!-- 商品の説明 -->
                <div class="item-description">
                    <h3 class="section-title">商品説明</h3>
                    <p class="description-text">{!! nl2br(e($item->description)) !!}</p>
                </div>

                <!-- 商品の情報 -->
                <div class="item-details">
                    <h3 class="section-title">商品の情報</h3>
                    <table class="details-table">
                        <tr>
                            <td class="table-label">カテゴリー</td>
                            <td class="table-value">
                                @foreach($item->categories as $category)
                                <span class="category-tag">{{ $category->name }}</span>
                                @if(!$loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td class="table-label">商品の状態</td>
                            <td class="table-value">{{ $item->condition->name }}</td>
                        </tr>
                    </table>
                </div>

                <!-- コメント欄 -->
                <div class="comments-section">
                    <h3 class="section-title">コメント ({{ $commentsCount }})</h3>

                    <!-- コメント一覧を先に表示 -->
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

                    <!-- コメント投稿フォーム -->
                    @auth
                    @if(!Auth::check() || $item->user_id !== Auth::id())
                    <form class="comment-form" action="{{ route('items.comment', $item->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="comment" class="form-label">商品へのコメント</label>
                            <textarea
                                name="comment"
                                id="comment"
                                rows="4"
                                class="form-textarea"
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
                    <div class="comment-disabled">自分の商品にはコメントできません。</div>
                    @endif
                    @else
                    <!-- ログイン前のコメントフォーム表示 -->
                    <div class="comment-form">
                        <div class="form-group">
                            <label class="form-label">商品へのコメント</label>
                            <textarea
                                rows="4"
                                class="form-textarea"
                                placeholder="コメントを入力してください"
                                disabled>{{ old('comment') }}</textarea>
                        </div>
                        <a href="/login" class="btn-comment-submit">
                            コメントを送信する
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
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
</script>
@endpush