<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>取引完了通知</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #FF5555;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #FF5555;
        }
        .content {
            padding: 30px 0;
        }
        h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #FF5555;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">COACHTECH フリマ</div>
        </div>
        
        <div class="content">
            <h1>取引が完了しました</h1>
            
            <p>{{ $buyerName }}様との取引が完了しました。</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="label">商品名</span>
                    <span class="value">{{ $itemName }}</span>
                </div>
                <div class="info-row">
                    <span class="label">購入者</span>
                    <span class="value">{{ $buyerName }}</span>
                </div>
                <div class="info-row">
                    <span class="label">完了日時</span>
                    <span class="value">{{ $completedAt }}</span>
                </div>
            </div>
            
            <p>購入者からの評価をお待ちしております。</p>
            <p>取引チャット画面にアクセスして、購入者を評価してください。</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">サイトにアクセス</a>
            </div>
        </div>
        
        <div class="footer">
            <p>このメールは自動送信されています。返信には対応しておりません。</p>
            <p>&copy; {{ date('Y') }} COACHTECH フリマ. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
