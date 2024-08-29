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
        Schema::create('running_code_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->integer('sequence')->default(1);
            $table->timestamps();

            $table->index('header_id', 'running_code_header_id_index');
            $table->index(['month', 'year'], 'running_code_detail_index');
            
            $table->foreign('header_id')->references('id')->on('running_code_headers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('running_code_details');
    }
};
