<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessDaysTable extends Migration
{
    public function up()
    {
        Schema::create('business_days', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_days');
    }
}
