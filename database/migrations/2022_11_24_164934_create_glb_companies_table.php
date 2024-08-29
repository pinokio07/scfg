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
        Schema::create('tps_glb_companies', function (Blueprint $table) {
            $table->id();
            $table->string('GC_Code')->nullable();
            $table->boolean('GC_IsActive')->default(false);
            $table->string('GC_Name')->nullable();
            $table->string('GC_BusinessRegNo')->nullable();
            $table->string('GC_BusinessRegNo2')->nullable();
            $table->string('GC_CustomsRegistrationNo')->nullable();
            $table->string('GC_Address1')->nullable();
            $table->string('GC_Address2')->nullable();
            $table->string('GC_City')->nullable();
            $table->string('GC_Phone')->nullable();
            $table->integer('GC_PostCode')->nullable();
            $table->string('GC_State')->nullable();
            $table->string('GC_Fax')->nullable();
            $table->string('GC_Email')->nullable();
            $table->string('GC_WebAddress')->nullable();
            $table->string('GC_RX_NKLocalCurrency')->nullable();
            $table->string('GC_RN_NKCountryCode')->nullable();
            $table->string('GC_TaxID')->nullable();
            $table->string('GC_Logo')->nullable();
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
        Schema::dropIfExists('tps_glb_companies');
    }
};
