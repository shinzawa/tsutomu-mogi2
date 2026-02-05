# 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```
make init
```

※Makefileは実行するコマンドを省略することができる便利な設定ファイルです。コマンドの入力を効率的に行えるようになります。<br>

## メール認証
mailhogというツールを使用しています。<br>

## ER図
![alt](ER.png)

## テストアカウント
name: 管理者1
email: admin1@gmail.com
password: password
-------------------------
name: 西 伶奈
email: reina.n@coachtech.com
password: password
-------------------------
name: 山田 太郎
email: taro.y@coachtech.com
password: password
-------------------------
name: 増田 一世
email: issei.m@coachtech.com
password: password
-------------------------
name: 山本 敬吉
email: keikichi.y@coachtech.com
password: password
-------------------------
name: 秋田 朋美
email: tomomi.a@coachtech.com
password: password
-------------------------
name: 中西 教夫
email: norio.n@coachtech.com
password: password
-------------------------

## PHPUnitを利用したテストに関して
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database demo_test;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```
