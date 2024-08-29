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
        Schema::create('tps_scheduler_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logable_id');
            $table->string('logable_type');
            $table->string('process');
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->text('info')->nullable();
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
        Schema::dropIfExists('tps_scheduler_log');
    }
};
