<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->text('customer_fiscal_information')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('order_datetime')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->dropColumn([
                'customer_name',
                'customer_fiscal_information',
                'delivery_address',
                'notes',
                'order_datetime',
            ]);
        });
    }
};
