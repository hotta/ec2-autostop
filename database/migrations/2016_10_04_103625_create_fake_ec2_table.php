<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFakeEc2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fake_ec2', function (Blueprint $table) {
            $table->text('nickname');       //  サーバーのニックネーム
            $table->text('instance_id');    //  インスタンスID
            $table->text('description');    //  説明
            $table->boolean('terminable');  //  終了可能
            $table->time('stop_at');        //  既定の停止時刻
            $table->ipAddress('private_ip');
            $table->enum('state', [         //  サーバーの動作状態
              'pending',      //  起動処理中
              'running',      //  動作中
              'rebooting',    //  再起動中
              'stopping',     //  停止処理中
              'stopped',      //  停止済み
              'shutting-down',//  削除処理中
              'terminated'    //  削除済み
            ]);

            $table->primary('instance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fake_ec2');
    }
}
