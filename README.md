# アプリケーション名
フリマアプリ

## プロジェクト概要
このフリマアプリは、ユーザーが商品を出品・購入できるフリマアプリケーションです。  
会員登録、商品の出品・検索・購入、いいね機能、コメント機能を備えています。  
応用機能として、メール認証（MailHog）を実装しています。支払い方法はコンビニ支払いとStripe決済（カード支払い）を実装しています。

## 環境構築
### Dockerビルド
1. 'git clone git@github.com:yokoda21/free-market.git

2. docker-compose up -d --build
*MySQLはOSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. 環境変数の設定 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成、.envに以下の環境変数を追加

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
・PHP8.2.29
・Laravel8.83.8
・MySQL8.0.26

## ER図
![ER図](free-market02(背景白).png)

## URL
・開発環境：http://localhost/
・phpMyAdmin : http://localhost:8080/
・MailHog（メール確認）：http://localhost:8025/

## 注意事項

### メール認証について
- 会員登録後、メール認証が必要です
- MailHogで認証リンクを確認してください：http://localhost:8025/

### Stripe決済について
- テスト環境では、実際の課金は発生しません
- テストカード番号（4242 4242 4242 4242）を使用してください
