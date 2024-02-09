<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessSocialMediaLinksTable extends Migration
{
    public function up()
    {
        Schema::create('business_social_media_links', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->string('link');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_social_media_links');
    }
}
