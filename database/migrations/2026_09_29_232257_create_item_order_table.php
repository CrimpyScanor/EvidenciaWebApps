<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_order', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->primary(['order_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_order');
    }
};
