@extends('layouts.app')

@section('title', '商品一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<main>
    <div class="item-index__content">
        <div class="item-tabs">
            <div class="item-tabs__menu">
                <div class="item-tabs__menu-item {{ request('tab') != 'mylist' ? 'item-tabs__menu-item--active' : '' }}">
                    <a href="{{ route('items.index') }}">おすすめ</a>
                </div>
                <div class="item-tabs__menu-item {{ request('tab') == 'mylist' ? 'item-tabs__menu-item--active' : '' }}">
                    <a href="{{ route('items.index', ['tab' => 'mylist']) }}">マイリスト</a>
                </div>
            </div>
        </div>

        
    

        @if($items->count() > 0)
        <div class="item-index__items">
            @foreach($items as $item)
            <div class="item-card">
                <a href="{{ route('items.show', $item->id) }}" class="item-card__link">
                    <div class="item-card__img">
                        @if($item->image_url)
                        <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}">
                        @else
                        <div class="item-card__img--placeholder">画像なし</div>
                        @endif
                        @if($item->is_sold)
                        <div class="item-card__sold">Sold</div>
                        @endif
                    </div>
                    <div class="item-card__content">
                        <div class="item-card__name">{{ $item->name }}</div>
                    </div>
                </a>
                <div class="item-card__actions">
                    @auth
                    <form class="like-form" data-item-id="{{ $item->id }}">
                        @csrf
                        <button type="button" class="like-btn {{ ($item->is_liked_by_user ?? false) ? 'like-btn--active' : '' }}">
                            <span class="like-icon">♥</span>
                            <span class="like-count">{{ $item->likes->count() }}</span>
                        </button>
                    </form>
                    @else
                    <div class="like-display">
                        <span class="like-icon">♥</span>
                        <span class="like-count">{{ $item->likes->count() }}</span>
                    </div>
                    @endauth
                </div>
                <div class="comment-count">
                    <span class="comment-icon">💬</span>
                    <span class="comment-count-number">{{ $item->comments->count() }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="no-items">
            @if(request('search'))
            <p>検索結果が見つかりませんでした。</p>
            @elseif(request('tab') == 'mylist')
            @auth
            <p>まだいいねした商品がありません。</p>
            @else
            <p>ログインしてマイリストを確認してください。</p>
            @endauth
            @else
            <p>商品がまだ登録されていません。</p>
            @endif
        </div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    // いいね機能のAjax処理
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.closest('.like-form').dataset.itemId;
            const likeIcon = this.querySelector('.like-icon');
            const likeCount = this.querySelector('.like-count');

            fetch(`/items/${itemId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // いいね状態の切り替え
                        this.classList.toggle('like-btn--active');
                        likeCount.textContent = data.likes_count;
                    } else {
                        // エラー処理
                        if (data.message) {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('エラーが発生しました。');
                });
        });
    });
</script>
@endpush