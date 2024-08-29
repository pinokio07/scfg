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
        Schema::create('tps_tariff_schemas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tariff_id');
            $table->integer('urut')->default(1);
            $table->string('name')->nullable();
            $table->decimal('rate', 11, 2)->default(0);
            $table->string('column')->default('CW');
            $table->integer('days')->length(2)->default(0);
            $table->boolean('as_one')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->timestamps();

            $table->index('tariff_id', 'tariff_schema_index');
            $table->foreign('tariff_id')->references('id')->on('tps_tariffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tps_tariff_schemas');
    }
};
