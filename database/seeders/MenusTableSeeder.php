<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Menu::firstOrCreate([
            'name' => 'admin',
        ]);
    }
}
