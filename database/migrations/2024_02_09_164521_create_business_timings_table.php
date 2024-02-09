<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTimingsTable extends Migration
{
    public function up()
    {
        Schema::create('business_timings', function (Blueprint $table) {
            $table->id();
            $table->integer('business_day_id');
            $table->time('start_time');
            $table->time('close_time');
            $table->integer('business_id');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_timings');
    }
}
