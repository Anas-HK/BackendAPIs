<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralContactsTable extends Migration
{
    public function up()
    {
        Schema::create('referral_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('status');
            $table->integer('is_deleted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referral_contacts');
    }
}
