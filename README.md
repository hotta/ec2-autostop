# 概要

AWS のインスタンスの一覧表示や起動／停止を行います。
基本は Web 画面で操作を行いますが、一部の動作は artisan コマンドでも行えます。

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
$ sudo cp -rp laravel-aws/* $LARAVEL_HOME
$ cp -rp laravel-aws/.env.default $LARAVEL_HOME/.env
$ cd $LARAVEL_HOME
$ vi .env （必要な変更を行う - 後述）
$ composer dump-autoload
$ sudo chmod -R a+w bootstrap/cache storage
$ sudo chmod +x artisan
$ ./artisan key:generate
$ ./artisan migrate
$ ./artisan | grep ec2
 ec2
  ec2:autostop        インスタンスの自動停止制御
  ec2:list            EC2 インスタンスの一覧を表示します
  ec2:reboot          インスタンスを再起動します
  ec2:start           インスタンスを起動します
  ec2:stop            インスタンスを停止します
$ ./artisan db:seed
$ ./artisan ec2:list （エミュレーター利用時の出力例）

+------------+-------------+---------+--------------+
| Nickname   | Private IP  | Status  | Instance ID  |
+------------+-------------+---------+--------------+
| dev-dummy1 | 172.16.0.8  | stopped | i-dev-dummy1 |
| dev-dummy2 | 172.16.0.99 | stopped | i-dev-dummy2 |
+------------+-------------+---------+--------------+
```

ここまで動いたら、ブラウザでアクセスできます。

# スクリーンショット

![Screenshot](https://github.com/hotta/images/blob/master/svrctl-screenshot.png?raw=true)


# 各インスタンスに設定するべきタグ

| タグ名      | 設定内容        | 設定値                          |
|:------------|:----------------|:--------------------------------|
| Name        | インスタンス名  | AWSコンソールに表示される文字列 | 
| Description | 説明文          | （日本語でOK）                  | 
| Terminable  | 停止可能        | true （GUI 制御対象）/ false    | 
| Stop_at     | 停止予定時刻    | HH:MM                           | 

# 実運用時の crontab 設定（例）

Laravel のスケジュール機能は使わないで単発コマンドのみで制御することを想定しているので、以下のようになります。

```crontab
ARTISAN='php /var/www/larave/artisan'
# 平日の朝に起動（起動時刻は cron で設定）
30 8 * * 1-5 $ARTISAN ec2:start -i dev1
0 9 * * 1-5 $ARTISAN ec2:start -i dev2
# 平日の指定時刻に停止（手動モードでない場合のみ。停止時刻はタグで設定）
1-51/10 15-23 * * 1-5 $ARTISAN ec2:autostop
```

# ec2:autostop コマンドの機能概要

- インスタンス一覧のうち、Terminable=true のものだけを対象とする
- 動作中のインスタンスのうち、現在時刻が stop_at を過ぎているものは停止する
- ただし「手動モード」のインスタンス(*1) については停止の対象としない
  - (*1) manualsレコードが存在するもの

# .env 設定内容（アプリケーション定義のもの）

| シンボル名            | 設定内容          | 設定値                                      |
|:----------------------|:------------------|:--------------------------------------------|
| APP_ROUTE_URL	        | http://FQDN       | サービスを提供するURL                       | 
| EC2_EMULATION          | true / false      | true の場合、AWSの動きをDBでシミュレートする| 
| AWS_REGION            | ap-northeast-1    | 使用するリージョン                          | 
| AWS_ACCESS_KEY_ID     | Access Key        | (AMIロールが付与されていない場合に指定）    | 
| AWS_SECRET_ACCESS_KEY | Secret Access Key | 同上                                        | 
| GUI_REMARKS           | 任意の文字列      | GUI 画面の最下段に表示する注意文言          | 

- AWS_* は、実際に AWS にアクセスする際にのみ必要。
