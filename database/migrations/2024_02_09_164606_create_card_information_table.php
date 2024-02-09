<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardInformationTable extends Migration
{
    public function up()
    {
        Schema::create('card_information', function (Blueprint $table) {
            $table->id();
            $table->string('card_holder_name');
            $table->string('card_number');
            $table->string('expiry');
            $table->integer('business_id');
            $table->integer('user_id');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_information');
    }
}
