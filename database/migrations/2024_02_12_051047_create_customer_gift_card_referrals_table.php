<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerGiftCardReferralsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_gift_card_referrals', function (Blueprint $table) {
            $table->id();
            $table->integer('gift_card_id');
            $table->integer('user_id');
            $table->integer('business_id');
            $table->decimal('amount', 10, 2);
            $table->string('card_holder_name');
            $table->string('card_number');
            $table->string('referral');
            $table->integer('user_id_referral');
            $table->integer('is_use');
            $table->integer('referral_contact_id');
            $table->string('payment_type');
            $table->string('status');
            $table->integer('is_deleted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_gift_card_referrals');
    }
}
