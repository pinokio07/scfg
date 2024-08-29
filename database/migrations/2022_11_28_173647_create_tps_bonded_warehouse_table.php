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
        Schema::create('tps_ref_bonded_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('tps_code')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->index('company_name', 'bw_company_index');
            $table->index('tps_code', 'bw_tp_code_index');
            $table->index('warehouse_code', 'bw_wh_code_index');
            $table->index('address', 'bw_address_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tps_ref_bonded_warehouses');
    }
};
