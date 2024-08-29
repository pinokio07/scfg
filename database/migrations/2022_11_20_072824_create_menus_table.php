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
      Schema::create('menus', function (Blueprint $table) {
          $table->id();
          $table->string('name')->unique();
          $table->timestamps();
      });

      Schema::create('menu_items', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('menu_id')->nullable();
          $table->string('title');
          $table->string('url');
          $table->string('route')->nullable()->default(null);
          $table->string('controller')->nullable();
          $table->string('permission')->nullable();
          $table->string('target')->default('_self');
          $table->string('icon_class')->nullable();
          $table->integer('parent_id')->nullable();
          $table->integer('order');
          $table->boolean('active')->default(true);
          $table->timestamps();
      });

      Schema::table('menu_items', function (Blueprint $table) {
          $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {        
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
