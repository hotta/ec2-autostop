<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manuals', function (Blueprint $table) {
            $table->increments('id');
            $table->date('t_date');         //  対象日付
            $table->text('instance_id');    //  インスタンスID
            $table->text('nickname');       //  サーバーのニックネーム

            $table->index('t_date', 'instance_id'); //  複合インデックス
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('manuals');
    }
}
