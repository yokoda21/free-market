# アプリケーション名
フリマアプリ

## プロジェクト概要
このフリマアプリは、ユーザーが商品を出品・購入できるフリマアプリケーションです。  
会員登録、商品の出品・検索・購入、いいね機能、コメント機能、取引チャット機能、取引評価機能を備えています。  
応用機能として、メール認証（MailHog）を実装しています。支払い方法はコンビニ支払いとStripe決済（カード支払い）を実装しています。

## 環境構築
### Dockerビルド
1. 'git clone git@github.com:yokoda21/free-market.git'
2. docker-compose up -d --build
*MySQLはOSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. 環境変数の設定
「.env.example」ファイルを「.env」ファイルにコピー、または新規作成し、以下を設定：
```env
・DB_CONNECTION=mysql  
・DB_HOST=mysql  
・DB_PORT=3306  
・DB_DATABASE=laravel_db  
・DB_USERNAME=laravel_user  
・DB_PASSWORD=laravel_pass 

メール認証はMailhogを使用しています。.envに以下のメール設定を追加
・MAIL_MAILER=smtp  
・MAIL_HOST=mailhog  
・MAIL_PORT=1025  
・MAIL_USERNAME=null  
・MAIL_PASSWORD=null  
・MAIL_ENCRYPTION=null  
・MAIL_FROM_ADDRESS="noreply@freemarket.local"  
・MAIL_FROM_NAME="${APP_NAME}"  

4. Stripe決済の設定を.envに追加  
・STRIPE_PUBLIC_KEY=your_stripe_public_key  
・STRIPE_SECRET_KEY=your_stripe_secret_key
```

※ Stripeのテストキーは以下から取得してください：  
https://dashboard.stripe.com/test/apikeys

テスト用カード番号：  
・カード番号：4242 4242 4242 4242  
・有効期限：任意の未来の日付（例：12/34）  
・CVC：任意の3桁の数字（例：123）  
5. php artisan key:generate  
6. php artisan migrate  
7. php artisan db:seed  
8. 画像保存ディレクトリのシンボリックリンク作成  
php artisan storage:link  

## 使用技術
・PHP 8.2.29
・Laravel 8.83.8
・MySQL 8.0.26
・Docker / Docker Compose
・Stripe API（決済処理）
・MailHog（メール送信テスト）

## ER図
![ER図](free-market03(背景白テーブル追加).png)

**主要テーブル：**
- users（ユーザー）
- profiles（プロフィール）
- items（商品）
- categories（カテゴリー）
- conditions（商品状態）
- item_categories（商品-カテゴリー中間テーブル）
- likes（いいね）
- comments（コメント）
- purchases（購入情報・取引管理）
- trade_messages（取引メッセージ）
- ratings（評価）

## URL
・開発環境：http://localhost/  
・phpMyAdmin : http://localhost:8080/  
・MailHog（メール確認）：http://localhost:8025/  

## テストアカウント

動作確認用のテストアカウントは、シーダー実行時に自動作成されます。

### 一般ユーザー

| ユーザー名 | メールアドレス | パスワード | 用途 |
|-----------|--------------|----------|------|
| 出品者太郎 | seller1@example.com | password | 商品出品用 |
| 購入者花子 | buyer1@example.com | password | 商品購入用 |
| テスト次郎 | test1@example.com | password | 一般テスト用 |

※ テストアカウントもメール認証が必要です。
**出品商品：**
- 出品者太郎：腕時計、HDD、玉ねぎ3束、革靴、ノートPC（5商品）
- 購入者花子：マイク、ショルダーバッグ、タンブラー、コーヒーミル、メイクセット（5商品）
- テスト次郎：出品なし

### 使用例
- **商品出品テスト**：seller1@example.com でログイン
- **商品購入・取引チャット**：buyer1@example.com でログイン → 商品購入 → マイページの「取引中の商品」から取引チャット
- **評価機能テスト**：取引完了後、購入者・出品者それぞれで評価を投稿
- **いいね・コメント機能**：どのアカウントでもOK

## 注意事項

### メール認証について
- 会員登録後、メール認証が必要です
- MailHogで認証リンクを確認してください：http://localhost:8025/

### 取引チャット機能について
- 商品購入後、マイページの「取引中の商品」タブから利用可能
- メッセージは400文字以内、画像添付可（1MBまで）
- 未読メッセージは赤いバッジで表示
- 最新メッセージのある取引が自動的に左側（先頭）に表示

### 評価機能について
- 取引完了後、購入者・出品者の双方が評価可能
- 5段階評価

### Stripe決済について
- テスト環境では、実際の課金は発生しません
- テストカード番号（4242 4242 4242 4242）を使用してください
