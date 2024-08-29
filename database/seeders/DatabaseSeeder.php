<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Crypt;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::firstOrCreate([
          'name' => Crypt::encrypt('Administrator'),
          'username' => 'admin',
          'email' => 'admin@admin.com',
        ],[
          'email_verified_at' => now(),
          'password' => bcrypt('rahasia'),
          'remember_token' => Str::random(10),
        ]);

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $user->assignRole($role);
    }
}
