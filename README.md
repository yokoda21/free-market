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
DB_CONNECTION=mysql  
DB_HOST=mysql  
DB_PORT=3306  
DB_DATABASE=laravel_db  
DB_USERNAME=laravel_user  
DB_PASSWORD=laravel_pass  

4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

## 使用技術
・PHP8.2.27
・Laravel8.83.8
・MySQL8.2.27

## ER図
![ER図](free-market02(背景白).png)

## URL
・開発環境：http://localhost/
・phpMyAdmin : http://localhost:8080/

