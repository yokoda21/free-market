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

                <!-- いいね・コメント部分 -->
                <div class="item-stats">
                    <div class="stats-item">
                        @auth
                        @if (!$isOwnItem)
                        <button class="like-btn {{ $isLiked ? 'liked' : '' }}"
                            data-item-id="{{ $item->id }}">
                            <img src="{{ asset('images/icons/star.png') }}"
                                alt="いいね"
                                class="like-icon {{ $isLiked ? 'liked-icon' : '' }}">
                        </button>
                        @else
                        <img src="{{ asset('images/icons/star.png') }}"
                            alt="いいね"
                            class="like-icon disabled">
                        @endif
                        @else
                        <img src="{{ asset('images/icons/star.png') }}"
                            alt="いいね"
                            class="like-icon">
                        @endauth
                        <span class="like-count">{{ $likesCount }}</span>
                    </div>

                    <div class="stats-item">
                        <img src="{{ asset('images/icons/comment.png') }}"
                            alt="コメント"
                            class="comment-icon">
                        <span class="comment-count">{{ $commentsCount }}</span>
                    </div>
                </div>

                <!-- 購入ボタン -->
                <div class="purchase-section">
                    @if($item->is_sold)
                    <div class="purchase-status">
                        <div class="sold-out">Sold Out</div>
                    </div>
                    @elseif(Auth::check() && $item->user_id == Auth::id())
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
                    @if($item->user_id !== Auth::id())
                    <form class="comment-form" action="{{ route('items.comment', $item->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="comment" class="form-label">商品へのコメント</label>
                            <textarea
                                name="comment"
                                id="comment"
                                rows="4"
                                class="form-textarea"
                                aria-required="true">{{ old('comment') }}</textarea>
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
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        console.log('=== いいね機能初期化 ===');

        // いいねボタンの初期化
        const likeBtn = document.querySelector('.like-btn');

        if (!likeBtn) {
            console.log('いいねボタンが見つからない、または無効です');
            return;
        }

        // CSRFトークン取得
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenMeta) {
            console.error('CSRFトークンが見つかりません');
            return;
        }

        const csrfToken = csrfTokenMeta.getAttribute('content');
        console.log('CSRFトークン取得成功');

        // いいねボタンのクリックイベント
        likeBtn.addEventListener('click', function() {
            console.log('=== いいねボタンクリック ===');

            const itemId = this.dataset.itemId;
            const likeIcon = this.querySelector('.like-icon');
            const likeCount = document.querySelector('.like-count');

            console.log('商品ID:', itemId);

            // APIリクエスト送信
            fetch('/items/' + itemId + '/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    console.log('レスポンスステータス:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('レスポンスデータ:', data);

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // いいねボタンとアイコンの状態更新
                    if (data.liked !== undefined) {
                        if (data.liked) {
                            likeBtn.classList.add('liked');
                            likeIcon.classList.add('liked-icon');
                        } else {
                            likeBtn.classList.remove('liked');
                            likeIcon.classList.remove('liked-icon');
                        }
                    } else if (data.is_liked !== undefined) {
                        // レスポンスのキー名が異なる場合の対応
                        if (data.is_liked) {
                            likeBtn.classList.add('liked');
                            likeIcon.classList.add('liked-icon');
                        } else {
                            likeBtn.classList.remove('liked');
                            likeIcon.classList.remove('liked-icon');
                        }
                    }

                    // いいね数を更新
                    if (data.likesCount !== undefined) {
                        likeCount.textContent = data.likesCount;
                    } else if (data.likes_count !== undefined) {
                        likeCount.textContent = data.likes_count;
                    }

                    console.log('=== 更新完了 ===');
                })
                .catch(function(error) {
                    console.error('エラー:', error);
                    alert('エラーが発生しました。ページを再読み込みしてください。');
                });
        });
    });
</script>
@endpush