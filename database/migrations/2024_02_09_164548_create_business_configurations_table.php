<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::create('business_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value');
            $table->integer('business_id');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_configurations');
    }
}
