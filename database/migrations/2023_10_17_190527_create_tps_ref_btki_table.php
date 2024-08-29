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
        Schema::create('tps_ref_btki', function (Blueprint $table) {
            $table->id();
            $table->string('POS_TARIFF');
            $table->text('UR_BRG_ID');
            $table->text('UR_BRG_EN');
            $table->decimal('BM_TARIFF', 15, 2)->default(0);
            $table->decimal('PPN_TARIFF', 15, 2)->default(0);
            $table->decimal('PPH_NPWP', 15, 2)->default(0);
            $table->decimal('PPH_NON_NPWP', 15, 2)->default(0);
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
        Schema::dropIfExists('tps_ref_btki');
    }
};
