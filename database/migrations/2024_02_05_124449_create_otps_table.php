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
            $table->string('code', 100)->nullable(false);
            $table->integer('status');
            $table->integer('is_used');
            $table->timestamp('created_at')->nullable(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
}
