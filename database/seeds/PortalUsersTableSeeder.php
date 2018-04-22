<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\User;

class PortalUsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0 || User::count() == 1) {
            $role = Role::where('name', 'admin')->firstOrFail();

            User::create([
                'name'           => 'Admin',
                'email'          => 'admin@test.com',
                'login'          => 'test_admin',
                'first_name'     => 'admin',
                'last_name'      => 'admin',
                'uuid'           => '00000000-0000-0000-0000-000000000000',
                'password'       => bcrypt('xxxyyy_'),
                'remember_token' => str_random(60),
                'role_id'        => $role->id,
            ]);
            User::create([
                'name'           => 'Test User',
                'email'          => 'test@test.com',
                'login'          => 'test_user',
                'first_name'     => 'Test',
                'last_name'      => 'User',
                'uuid'           => '00000000-0000-0000-0000-000000000000',
                'password'       => bcrypt('test157'),
                'remember_token' => str_random(60),
                'role_id'        => $role->id,
            ]);
        }
    }
}
