<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('plan_id')->nullable();
            $table->integer('subclass_id')->nullable();
            $table->integer('customer_quotation_id')->nullable();
            $table->json('request_json')->nullable();
            $table->json('response_json')->nullable();
            $table->json('additional_info_json')->nullable();
            $table->json('payment_method_info_json')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('step_no')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('quotation_requested_at')->useCurrent();
            $table->timestamp('quotation_approved_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
