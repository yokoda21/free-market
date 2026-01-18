<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COACHTECH')</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('styles')
</head>

<body>
    <!-- 取引画面専用ヘッダー（ロゴのみ） -->
    <header class="header header--trade">
        <div class="header__inner">
            <div class="header__logo">
                <a href="{{ route('items.index') }}" class="header__logo-link">
                    <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo-img">
                </a>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
