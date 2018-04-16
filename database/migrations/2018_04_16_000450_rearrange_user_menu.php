<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RearrangeUserMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $id = DB::table('menu_items')->insertGetId(
            array(
                'menu_id' => 1,
                'title' => 'User Management',
                'target' => '_self',
                'icon_class' => 'voyager-person',
                'order' => 8,
                'url' => ''
            )
        );
        $roleId = DB::table('menu_items')->where('title', 'Roles')->value('id');
        DB::table('menu_items')->where('id', $roleId)->update(['parent_id' => $id]);
        $usersId = DB::table('menu_items')->where('title', 'Users')->value('id');
        DB::table('menu_items')->where('id', $usersId)->update(['parent_id' => $id, 'order' => 1]);
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
