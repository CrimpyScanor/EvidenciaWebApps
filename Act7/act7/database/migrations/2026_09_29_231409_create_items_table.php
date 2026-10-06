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
    Schema::create('items', function (Blueprint $table) { 
      $table->id(); 
      $table->string('name'); 
      $table->text('description'); 
      $table->string('type'); 
      $table->unsignedInteger('price'); 
      $table->unsignedInteger('quantity_in_stock'); 

      $table->unsignedBigInteger('order_id'); 
      $table->foreign('order_id') 
        ->references('id') 
        ->on('orders') 
        ->onDelete('cascade'); 
    }); 
  } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
};
