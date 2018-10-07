<?php

use TCG\Voyager\Models\MenuItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMenuItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // MenuItem::where('title', 'Pages')->delete();
        // MenuItem::where('title', 'Posts')->delete();
        // MenuItem::where('title', 'Categories')->delete();
        // MenuItem::where('title', 'Compass')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
