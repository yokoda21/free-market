# アプリケーション名
フリマアプリ
## 環境構築
### Dockerビルド
1. git clone　リンク
git@github.com:yokoda21/free-market.git

2. docker-compose up -d --build
*MySQLはOSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成、.envに以下の環境変数を追加

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

5. php artisan key:generate
6. php artisan migrate
7. php artisan db:seed

## 使用技術
・PHP8.2.27
・Laravel8.83.8
・MySQL8.2.27

## ER図
![ER図](free-market02(背景白).png)

## URL
・開発環境：http://localhost/
・phpMyAdmin : http://localhost:8080/

