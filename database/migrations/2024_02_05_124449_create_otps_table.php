<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('business_id')->default(0);
            $table->string('email')->nullable(false); // Add email column
            $table->string('code', 100)->nullable(false);
            $table->integer('UUID');
            $table->integer('status');
            $table->integer('is_used')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
}
