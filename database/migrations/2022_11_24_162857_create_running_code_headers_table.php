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
        Schema::create('running_code_headers', function (Blueprint $table) {
            $table->id();
            $table->string('module')->nullable();
            $table->string('name')->nullable();
            $table->text('pattern')->nullable();
            $table->set('reset', ['month', 'year', 'never'])->default('never');
            $table->integer('leading_zero')->default(0);
            $table->timestamps();

            $table->index(['module', 'name'], 'running_code_header_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('running_code_headers');
    }
};
