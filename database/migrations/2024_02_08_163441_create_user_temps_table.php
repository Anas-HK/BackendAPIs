<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_temps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable(false);
            $table->string('email', 100)->nullable(false);
            $table->string('password', 255)->nullable(false);
            $table->string('phone', 100)->nullable(false);
            $table->date('date_of_birth');
            $table->integer('status');
            $table->integer('user_type_id');
            $table->integer('category_id');
            $table->integer('business_id')->default(null);
            $table->integer('is_deleted')->default(0);
            $table->integer('consent');
            $table->integer('verified')->default(0);
            $table->boolean('UUID');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_temps');
    }
};
