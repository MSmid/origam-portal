<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulerTable extends Migration
{
    protected $periods = [
      'hourly', 'daily', 'weekly', 'monthly'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedulers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->timestamp('start_at');
            $table->enum('period', $this->periods)->default($this->periods[1]);
            $table->integer('data_source_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('data_source_id')->references('id')->on('data_sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedulers');
    }
}
