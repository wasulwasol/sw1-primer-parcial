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
        Schema::create('evento_fotografos', function (Blueprint $table) {
            $table->unsignedBigInteger('fotografos_id');
            $table->unsignedBigInteger('eventos_id');
            
            $primary = ['fotografos_id', 'eventos_id'];
            
            $table->foreign('fotografos_id')->references('id')->on('fotografos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('eventos_id')->references('id')->on('eventos')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('evento_fotografos');
    }
};
