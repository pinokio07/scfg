<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Spatie\Permission\Models\Permission;
use App\Models\Menu;
use App\Models\MenuItem;

class AdminMenusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Menu::all();
        $items->makeHidden(['created_at','updated_at']);

        return view('admin.menus.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menu = new Menu;

        // dd($menu);
        
        return view('admin.menus.create-edit', compact('menu'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
          'name' => 'required'
        ]);

        $menu = Menu::updateOrCreate(['name' => $request->name]);

        return redirect()->route('admin.menus');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu)
    {
        return view('admin.menus.view', compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        return view('admin.menus.create-edit', compact('menu'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $menu)
    {
        foreach ($menu->items as $item) {
          $group = Permission::where('name', $item->permission)->first()->group;
          $delPermit = Permission::where('group', $group)->delete();
          $item->delete();
        }
        $menu->delete();
        return redirect('/administrator/menus');
    }
    
}
