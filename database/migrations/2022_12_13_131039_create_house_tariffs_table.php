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
        Schema::create('tps_house_tariffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('house_id');
            $table->integer('urut')->default(1);
            $table->string('item');
            $table->integer('days')->nullable();
            $table->decimal('weight', 20, 2)->nullable();
            $table->decimal('rate', 20, 2)->nullable();
            $table->decimal('total', 20, 2)->default(0);
            $table->boolean('is_estimate')->default(true);
            $table->boolean('is_vat')->default(false);
            $table->timestamps();

            $table->index('house_id', 'house_tariff_index');
            $table->foreign('house_id')->references('id')->on('tps_houses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('house_tariffs');
    }
};
