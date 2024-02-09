<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessFeatureImagesTable extends Migration
{
    public function up()
    {
        Schema::create('business_feature_images', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->string('image');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_feature_images');
    }
}
