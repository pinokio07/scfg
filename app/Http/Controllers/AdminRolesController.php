<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;


class AdminRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Role::where('name', '<>', 'super-admin')->orderBy('name')->get();
        $items->makeHidden(['guard_name', 'created_at','updated_at']);

        return view('admin.roles.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $role = new Role;
        $permissions = Permission::orderBy('group')->get();
        $users = User::all();

        return view('admin.roles.create-edit', compact(['role', 'permissions', 'users']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $role = Role::updateOrCreate([
                      'name' => $request->name
                    ],[
                      'guard_name' => 'web'
                    ]);
        if($request->permission != ''){
          foreach ($request->permission as $permission) {
            $role->givePermissionTo($permission);
          }
        }

        return redirect('/administrator/roles/'.$role->id.'/edit')->with('sukses', 'Create Role Success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $role->load('permissions');

        return view('admin.roles.view', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('group')->get();
        $users = User::all();

        return view('admin.roles.create-edit', compact(['role', 'permissions', 'users']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
          'name' => [
            'required',
            Rule::unique('roles')->ignore($role),
          ]
        ]);

        if($validator->fails()){
          return redirect()->back()->with('gagal', 'Role name already taken.');
        } else {
          $role->name = $request->name;
          $role->save();
          
          if($request->permission != ''){
             $role->syncPermissions($request->permission);
             $role->users()->sync($request->users);
          }
          return redirect('/administrator/roles/'.$role->id.'/edit')->with('sukses', 'Update Role Success.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

        return redirect('/administrator/roles')->with('sukses', 'Delete Role Success.');
    }
}
