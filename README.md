# 概要

このプログラムは AWS の EC2 インスタンスの起動／停止制御を行います。基本的には Web 画面で操作しますが、一部の動作は artisan コマンドでも行えます。

IAM ロールを持つインスタンス以外で実行する場合、IAM アカウント情報の設定が必要です。AWS のアカウントがない場合でも、DB を使って EC2 の動作をエミュレートすることも可能です。

# 前提条件

- ( Vagrant + VirtualBox + ) CentOS7.x + Laravel の環境
  - https://github.com/hotta/laravel-centos7 の環境で動作を確認しています。
  - 想定環境：php-7.x + nginx + php-fpm + DB(PostgreSQL) + laravel-5.5.x + php-sdk-php-laravel-3.0

# 環境構築手順

```bash
$ git clone https://github.com/hotta/ec2-autostop.git
$ echo 'export LARAVEL_HOME=/var/www/laravel' >> ~/.bashrc && . ~/.bashrc
$ cp -rp ec2-autostop/* $LARAVEL_HOME
$ cd $LARAVEL_HOME
$ vi .env （IAM アカウント等の設定を行う - 後述）
（ローカルで模擬環境を構築する場合、最低でも EC2_EMULATION=true を指定すること）
$ touch storage/logs/laravel.log
$ sudo chown -R nginx bootstrap/cache storage
$ sudo chmod -R a+w bootstrap/cache storage
$ sudo chmod +x artisan
$ composer dump-autoload
$ ./artisan migrate
$ ./artisan | grep ec2
 ec2
  ec2:autostop        インスタンスの自動停止制御
  ec2:list            EC2 インスタンスの一覧を表示します
  ec2:reboot          インスタンスを再起動します
  ec2:start           インスタンスを起動します
  ec2:stop            インスタンスを停止します
$ ./artisan db:seed

# 使い方

$ ./artisan ec2:list 

-----------+-------------+---------+-------------+---------+-------+
| Nickname  | Private IP  | Status  | Instance ID | Stop at | Term  |
+-----------+-------------+---------+-------------+---------+-------+
| dev1      | 172.16.0.8  | running | i-dev1      | 14:00   | true  |
| dev2      | 172.16.0.91 | running | i-dev2      | 15:00   | false |
| dev3      | 172.16.0.92 | running | i-dev3      | 16:00   | true  |
| dev4      | 172.16.0.93 | running | i-dev4      | 17:00   | false |
| dev5      | 172.16.0.94 | running | i-dev5      | 18:00   | true  |
| dev-test1 | 172.16.1.8  | running | i-dev-test1 |         | false |
| dev-web1  | 172.16.0.8  | running | i-dev-web1  |         | false |
+-----------+-------------+---------+-------------+---------+-------+
（上記はエミュレーションモード利用時の表示）

$ ./artisan ec2:autostop --help
Usage:
  ec2:autostop [options]

Options:
  -i, --instanceid=INSTANCEID  対象のインスタンスID
      --nickname=NICKNAME      対象インスタンスのニックネーム（これらのいずれかを指定）
  -h, --help                   Display this help message
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi                   Force ANSI output
      --no-ansi                Disable ANSI output
  -n, --no-interaction         Do not ask any interactive question
      --env[=ENV]              The environment the command should run under
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  インスタンスの自動停止制御
```

ここまで動いたら、ブラウザでアクセスできます。

# スクリーンショット

![Screenshot](https://github.com/hotta/images/blob/master/svrctl-screenshot.png?raw=true)

artisan ec2:list ではすべてのインスタンスを表示しますが、Web インターフェイスで表示されるのは Terminable（停止可能）が true のインスタンスのみです。

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

- 自分が保有するインスタンスのうち、以下の条件をすべて満たすものを停止する。
  - Terminable=true のもの
  - 動作中のもの
  - 現在時刻が stop_at を過ぎているもの
  - 「手動モード」でないもの（＝自動停止モード）
    - GUI 画面で「手動モードへ」を選択したインスタンスについては、その日の運用が「手動モード」に切り替わります。
    - 「手動モード」のインスタンスは、利用者が管理画面で明示的に「停止」をクリックしない限り動作し続けます。
    - 日をまたいだ場合、当該インスタンスは「自動停止モード」に戻ります。

# .env 設定内容（アプリケーション定義のもの）

| シンボル名            | 設定内容          | 設定値                                      |
|:----------------------|:------------------|:--------------------------------------------|
| DB_USERNAME  	        | vagrant           | ansible_user_id                             | 
| APP_ROUTE_URL	        | http://FQDN       | サービスを提供するURL                       | 
| EC2_EMULATION         | true / false      | true の場合、AWSの動きをDBでシミュレートする| 
| AWS_REGION            | ap-northeast-1    | 使用するリージョン                          | 
| AWS_ACCESS_KEY_ID     | Access Key        | (IAMロールが付与されていない場合に指定）    | 
| AWS_SECRET_ACCESS_KEY | Secret Access Key | 同上                                        | 
| GUI_REMARKS           | 任意の文字列      | GUI 画面の最下段に表示する注意文言          | 
