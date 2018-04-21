<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDashboardSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dashboard_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('slug_datatable_name');
            $table->integer('data_source_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('data_source_id')->references('id')->on('data_sources')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_dashboard_settings');
    }
}
