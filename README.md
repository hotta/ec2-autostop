# 概要

AWS のインスタンスの一覧表示や起動／停止を行います。基本は Web 画面で操作を行いますが、一部の動作は artisan コマンドでも行えます。

# ベース環境

- Vagrant + VirtualBox を使った CentOS7.x 環境の作成
  - https://github.com/hotta/vagrant-cent72-box
  - CentOS 7.x + epel + remi + git + ansible 2.x になります。
- 上記を利用した Laravel 開発環境の環境
  - https://github.com/hotta/laravel-centos7
  - DB のデフォルト(SQLite)を変更する場合 
    - /etc/ansible/host_vars/localhost-XXXXX.yml -> localhost.yml に設定
  - php-7.x + nginx + php-fpm + laravel-5.2.x + php-sdk-php-laravel-3.0 になります。
  - 上記手順を使った場合、$LARAVEL_HOME は /var/www/laravel になります。

# 環境構築手順

```bash
$ git clone git@github.com:hotta/laravel-aws.git
$ cp -rp laravel-aws/* $LARAVEL_HOME
$ cp -rp laravel-aws/.??* $LARAVEL_HOME
$ cd $LARAVEL_HOME
$ cp .env.default .env
$ vi .env （必要な変更を行う）
$ touch database/database.sqlite（デフォルトの SQLite を使う場合）
$ ./artisan migrate
$ ./artisan | grep ec2
 ec2
  ec2:autostop        インスタンスの自動停止制御
  ec2:list            EC2 インスタンスの一覧を表示します
  ec2:reboot          インスタンスを再起動します
  ec2:start           インスタンスを起動します
  ec2:stop            インスタンスを停止します
$ ./artisan db:seed
$ ./artisan ec2:list （スタブ利用時の出力例）

+------------+-------------+---------+--------------+
| Nickname   | Private IP  | Status  | Instance ID  |
+------------+-------------+---------+--------------+
| dev-dummy1 | 172.16.0.8  | stopped | i-dev-dummy1 |
| dev-dummy2 | 172.16.0.99 | stopped | i-dev-dummy2 |
+------------+-------------+---------+--------------+
```

# スクリーンショット

![Screenshot](https://github.com/hotta/images/blob/master/svrctl-screenshot.png?raw=true)

# 実環境(AWS)に対して使用する場合

## .envへの追加設定

- AWS への API リクエスト権限のない（AMI ロールを付与されていない）ホストから実行する場合、AWS_ACCESS_KEY_ID と AWS_SECRET_ACCESS_KEY を追加
- 東京リージョン利用の場合、 AWS_REGION=ap-northeast-1 を追加
- AWS_EC2_STUB を false に変更またはコメントアウト

## 各インスタンスに設定するべきタグ

```
+-------------+-----------------+---------------------------------+
| タグ名      | 設定内容        | 設定値                          |
+-------------+-----------------+---------------------------------+
| Name        | インスタンス名  | AWSコンソールに表示される文字列 | 
| Description | 説明文          | （日本語でOK）                  | 
| Terminable  | 停止可能        | true （GUI 制御対象）/ false    | 
| Stop_at     | 停止予定時刻    | HH:MM                           | 
+-------------+-----------------+---------------------------------+
```
