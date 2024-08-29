<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use \Spatie\Permission\Models\Permission;
use App\Models\MenuItem;

class AdminPermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Permission::orderBy('group')->orderBy('name')->get();
        $items->makeHidden(['guard_name', 'created_at','updated_at']);

        return view('admin.permissions.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = new Permission;
        $groups = Permission::distinct('group')->pluck('group');

        return view('admin.permissions.create-edit', compact(['permission', 'groups']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
                              'name' => 'required|unique:permissions,name',
                            ]);
      if($validator->fails()){
        return redirect('/administrator/permissions/create')->withErrors($validator);
      } else {
        
        $permission = Permission::create([
                                'name' => $request->name,
                                'guard_name' => 'web',
                                'group' => $request->group
                              ]);
        
        return redirect('/administrator/permissions')->with('sukses', 'Create Permission Success.');
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        $itemMenus = MenuItem::where('permission', $permission->name)
                             ->orderBy('title')
                             ->get();
        $permission->load('roles');

        return view('admin.permissions.view', compact(['permission', 'itemMenus']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        $groups = Permission::distinct('group')->pluck('group');

        return view('admin.permissions.create-edit', compact(['permission', 'groups']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
                              'name' => [
                                'required',
                                Rule::unique('permissions')->ignore($permission),
                              ]
                            ]);
        if($validator->fails()){
          return redirect('/administrator/permissions/'.$permission->id.'/edit')->withErrors($validator);
        } else {         

          foreach(MenuItem::where('permission', $permission->name)->get() as $menu){
            $menu->permission = $request->name;
            $menu->save();
          }
          
          $permission->name = $request->name;
          $permission->group = $request->group;
          $permission->save();
  
          return redirect('/administrator/permissions/'.$permission->id.'/edit')->with('sukses', 'Update Permission Success.');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        foreach(MenuItem::where('permission', $permission->name)->get() as $menuItem){
          $menuItem->permission = NULL;
          $menuItem->save();
        }
        $permission->roles()->detach();
        $permission->delete();

        return redirect('/administrator/permissions')->with('sukses', 'Delete Permission Success.');
    }
}
