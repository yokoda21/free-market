<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('styles')
</head>

<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="header__inner">
            <!-- ヘッダーアイコン -->
            <div class="header__logo">
                <a href="{{ route('items.index') }}" class="header__logo-link">
                    <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
                </a>
            </div>

            <!-- ヘッダーサーチ -->
            <div class="header__search">
                <form action="/" method="GET" class="header__search-form">
                    <input
                        type="text"
                        name="search"
                        class="header__search-input"
                        placeholder="なにをお探しですか？"
                        value="{{ request('search') }}">
                </form>
            </div>

            <!-- ヘッダーナビ -->
            <nav class="header__nav">
                <ul class="header__nav-list">
                    @guest
                    <li class="header__nav-item">
                        <a href="{{ route('login') }}" class="header__nav-link">ログイン</a>
                    </li>
                    @else
                    <li class="header__nav-item">
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="header__nav-link">ログアウト</button>
                        </form>
                    </li>
                    @endguest
                    <li class="header__nav-item">
                        <a href="{{ route('user.profile') }}" class="header__nav-link">マイページ</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('items.create') }}" class="header__nav-link header__nav-link--sell">出品</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main">
        @yield('content')
    </main>

    <!-- JavaScript Files -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>