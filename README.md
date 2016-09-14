## What is this?

AWS のインスタンス一覧を表示する、Laravel コンソールアプリのサンプルです。

## Prerequisite

- Vagrant + VirtualBox を使った CentOS7.x 環境の作成
  - https://github.com/hotta/vagrant-cent72-box
  - CentOS 7.x + epel + remi + git + ansible 2.x になります。
- 上記を利用した Laravel 開発環境の環境
  - https://github.com/hotta/laravel-centos7
  - php-7.x + nginx + php-fpm + laravel-5.2.x + php-sdk-php-laravel-3.0 になります。

## Quick start

### 上記手順を使った場合、LARAVEL_HOME は /var/www/laravel になります。

```bash
$ git clone git@github.com:hotta/laravel-aws.git
$ cp -rp laravel-aws/* /path/to/LARAVEL_HOME
$ vi /path/to/LARAVEL_HOME/.env
(東京リージョン利用の場合 AWS_REGION=ap-northeast-1 を追加)
$ cd /path/to/LARAVEL_HOME
$ php artisan ec2:list
（出力例）
Tag Name      Private IP    Status         Instance ID
------------------------------------------------------------
dev-ad        172.16.1.9    stopped    i-0XXXXXXXXXXXXXXe3
dev-comgw     172.16.1.12   stopped    i-0XXXXXXXXXXXXXX22
dev-comsh     172.16.1.10   stopped    i-0XXXXXXXXXXXXXX96

```

## AWS 認証情報

AWS への API リクエスト権限のない（AMI ロールを付与されていない）ホストから実行する場合は、 [~/.aws/credential](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html#credential-profiles) を設定してください。

