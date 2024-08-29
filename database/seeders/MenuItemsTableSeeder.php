<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;

class MenuItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();

        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'title'   => 'Dashboard',
            'url'     => '/administrator',
            'route'   => 'admin.dashboard',
        ]);
        if (!$menuItem->exists) {
            $menuItem->fill([
                'target'     => '_self',
                'icon_class' => 'fas fa-tachometer-alt',
                'parent_id'  => null,
                'order'      => 1,
            ])->save();
        }

        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'title'   => 'Users',
            'url'     => '/administrator/users',
            'route'   => null,
        ]);
        if (!$menuItem->exists) {
            $menuItem->fill([
                'target'     => '_self',
                'icon_class' => 'fas fa-users',
                'parent_id'  => null,
                'order'      => 2,
            ])->save();
        }
        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'title'   => 'Roles',
            'url'     => '/administrator/roles',
            'route'   => null,
        ]);
        if (!$menuItem->exists) {
            $menuItem->fill([
                'target'     => '_self',
                'icon_class' => 'fas fa-lock',
                'parent_id'  => null,
                'order'      => 3,
            ])->save();
        }
        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'title'   => 'Permissions',
            'url'     => '/administrator/permissions',
            'route'   => null,
        ]);
        if (!$menuItem->exists) {
            $menuItem->fill([
                'target'     => '_self',
                'icon_class' => 'fas fa-user-lock',
                'parent_id'  => null,
                'order'      => 4,
            ])->save();
        }
        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'title'   => 'Menus',
            'url'     => '/administrator/menus',
            'route'   => null,
        ]);
        if (!$menuItem->exists) {
            $menuItem->fill([
                'target'     => '_self',
                'icon_class' => 'fas fa-list',
                'parent_id'  => null,
                'order'      => 5,
            ])->save();
        }
        
    }
}
