<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__utilities">
                <a class="header__logo" href="{{ route('items.index') }}">
                    coachtechフリマ
                </a>
            </div>
            <div class="header__nav">
                <form class="search-form" action="{{ route('items.index') }}" method="GET">
                    <div class="search-form__item">
                        <input class="search-form__item-input" type="text" name="search" placeholder="なにをお探しですか？" value="{{ request('search') }}">
                    </div>
                    @if(request('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif
                </form>
                <nav>
                    <ul class="header-nav">
                        @auth
                        <li class="header-nav__item">
                            <form class="form" action="{{ route('logout') }}" method="post">
                                @csrf
                                <button class="header-nav__link">ログアウト</button>
                            </form>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('user.profile') }}">マイページ</a>
                        </li>
                        @else
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('login') }}">ログイン</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('register') }}">会員登録</a>
                        </li>
                        @endauth
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('items.create') }}">出品</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

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

            <div class="item-index__heading">
                @if(request('search'))
                <h2>「{{ request('search') }}」の検索結果</h2>
                @elseif(request('tab') == 'mylist')
                <h2>マイリスト</h2>
                @else
                <h2>おすすめ</h2>
                @endif
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
                            <div class="item-card__price">¥{{ number_format($item->price) }}</div>
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
</body>

</html>