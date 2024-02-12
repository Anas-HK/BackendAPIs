<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemImagesTable extends Migration
{
    public function up()
    {
        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('item_id');
            $table->string('image');
            $table->string('default');
            $table->string('status');
            $table->integer('is_deleted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_images');
    }
}
