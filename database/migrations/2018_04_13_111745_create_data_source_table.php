<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('url', 255);
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('data_source_type_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('data_source_type_id')->references('id')->on('data_source_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_sources');
    }
}
