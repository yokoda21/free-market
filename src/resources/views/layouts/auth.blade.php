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

<body class="auth-body">
    <!-- 認証画面専用ヘッダー -->
    <header class="auth-header">
        <div class="auth-header__inner">
            <!-- ヘッダーロゴのみ -->
            <div class="auth-header__logo">
                <a href="{{ route('items.index') }}" class="auth-header__logo-link">
                    <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="auth-header__logo-img">
                </a>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="auth-main">
        @yield('content')
    </main>

    <!-- JavaScript Files -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>