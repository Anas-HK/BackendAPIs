<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('feedback_statuses', function (Blueprint $table) {
            $table->id();
            $table->text('feedback');
            $table->integer('user_id');
            $table->integer('status');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback_statuses');
    }
}
