<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftcardsTable extends Migration
{
    public function up()
    {
        Schema::create('giftcards', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->string('category');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->date('expiry');
            $table->string('image');
            $table->string('status');
            $table->integer('is_deleted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('giftcards');
    }
}
