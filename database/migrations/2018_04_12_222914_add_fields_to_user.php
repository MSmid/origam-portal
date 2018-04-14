<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUser extends Migration
{
  /**
   * Run the migrations.
   */
  public function up()
  {
      Schema::table('users', function ($table) {
          $table->string('first_name')->after('name');
          $table->string('last_name')->after('first_name');
          $table->string('login')->after('email');
          $table->uuid('uuid')->after('updated_at');
      });
  }

  /**
   * Reverse the migrations.
   */
  public function down()
  {
    Schema::table('users', function ($table) {
        $table->dropColumn('first_name');
        $table->dropColumn('last_name');
        $table->dropColumn('login');
        $table->dropColumn('uuid');
    });
  }
}
