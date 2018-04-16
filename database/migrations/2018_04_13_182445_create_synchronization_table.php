<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSynchronizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $status_types = ['none' ,'success', 'failed', 'in-progress'];

    public function up()
    {
        Schema::create('synchronizations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('started_at')->useCurrent()->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('message')->nullable();
            $table->integer('data_source_id')->unsigned()->index();
            $table->enum('status',  $this->status_types)->default($this->status_types[0]);
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
        Schema::dropIfExists('synchronizations');
    }
}
