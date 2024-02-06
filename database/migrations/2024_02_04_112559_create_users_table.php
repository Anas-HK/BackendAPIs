<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable(false);
            $table->string('email', 100)->nullable(false);
            $table->string('password', 255)->nullable(false);
            $table->string('phone', 100)->nullable(false);
            $table->date('date_of_birth');
            $table->integer('status');
            $table->integer('user_type_id');
            $table->integer('category_id');
            $table->integer('is_deleted');
            $table->integer('consent');
            $table->timestamps();
        });

        // Set default value for date_of_birth column
        DB::statement("ALTER TABLE users MODIFY date_of_birth DATETIME DEFAULT NOW()");
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
