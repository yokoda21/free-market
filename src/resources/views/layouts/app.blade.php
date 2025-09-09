<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'フリマアプリ')</title>

    <!-- 基本的なリセットCSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* 簡易ヘッダー */
        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
            margin-bottom: 20px;
        }

        .header h1 {
            text-align: center;
            color: #007bff;
        }

        /* 基本的なボタンスタイル */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        /* 基本的なカードスタイル */
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* エラーメッセージ */
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <!-- 簡易ヘッダー -->
    <div class="header">
        <div class="container">
            <h1>COACHTECHフリマアプリ</h1>
        </div>
    </div>

    <!-- メインコンテンツ -->
    <main class="container">
        <!-- フラッシュメッセージ表示 -->
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
        @endif

        <!-- 各ページのコンテンツ -->
        @yield('content')
    </main>

    <!-- 基本的なJavaScript（必要に応じて） -->
    @yield('scripts')
</body>

</html>