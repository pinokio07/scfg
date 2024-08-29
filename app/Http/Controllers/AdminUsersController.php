<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\GlbCompany;
use App\Models\GlbBranch;
use DataTables;
use Crypt;
use Str;


class AdminUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        if($request->ajax()){
          $query = User::with('roles');

          return DataTables::eloquent($query)
                           ->addColumn('cekbox', function($row){
                             $chk = '<input type="checkbox"
                                            id="chk_'.$row->id.'">';
                             return $chk;
                           })
                           ->addColumn('name', function($row){
                            $name = '<a href="'.route('admin.users.edit', $row->id).'">'.$row->name.'</a>';

                            return $name;
                           })
                           ->addColumn('avatar', function($row){
                             $img = '<img src="'.$row->getAvatar().'"
                                          class="img-fluid img-circle elevation-2"
                                          style="max-height:80px;width:auto;">';

                             return $img;
                           })
                           ->addColumn('created_at', function($row){
                             return $row->created_at->locale('id_ID')
                                        ->format('d-m-Y');
                           })
                           ->addColumn('updated_at', function($row){
                             if($row->created_at == $row->updated_at){
                               $lastLogin = 'Not Login';
                             } else {
                               $lastLogin = $row->updated_at->diffForHumans();
                             }

                             return $lastLogin;
                           })
                           ->addColumn('roles', function($row){
                             
                             $roles = $row->roles;
                             if($roles){
                                $ro = '';
                               foreach ($roles as $role) {
                                 $ro .= '<span class="badge badge-info">'.$role->name.'</span></br>';
                               }
                             } else {
                               $ro = '<span class="badge badge-danger">None</span>';
                             }
                             return $ro;
                           })
                           ->addColumn('actions', function($row){
                            $btn = '<a href="'.url()->current().'/'.$row->id.'" 
                                       class="btn btn-xs elevation-2 btn-info elevation-2">
                                       <i class="fas fa-eye"></i> View</a> ';
                            $btn .= '<a href="'.url()->current().'/'.$row->id.'/edit" 
                            class="btn btn-xs elevation-2 btn-warning elevation-2">
                            <i class="fas fa-edit"></i> Edit</a> ';

                            $btn .= '<a data-href="'.url()->current().'/'.$row->id.'" 
                            class="btn btn-xs elevation-2 btn-danger elevation-2 delete">
                            <i class="fas fa-trash"></i> Delete</a> ';
                            
                            return $btn;
                           })
                           ->rawColumns(['cekbox', 'name', 'avatar', 'roles', 'actions'])
                           ->toJson();
        }

        $items = collect([
          'cekbox' => 'cekbox',
          'name' => 'Name',
          'email' => 'Email',
          'avatar' => 'Avatar',
          'created_at' => 'Created At',
          'updated_at' => 'Last Activity',
          'roles' => 'Roles'
        ]);

        return view('admin.users.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = new User;
        $roles = Role::where('name', '<>', 'super-admin')->get();
        $branches = GlbBranch::with('company')->where('CB_IsActive', true)->get();

        return view('admin.users.create-edit', compact(['user', 'roles', 'branches']));
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
                            'name' => 'required',
                            'username' => 'required|unique:users,username',
                            'email' => 'required|unique:users,email',
                            'password' => 'required',          
                          ]);
        $newUser = User::updateOrCreate([
                          'email' => $data['email'],
                        ],[
                          'username' => $data['username'],
                          'name' => Crypt::encrypt($data['name']),
                          'password' => bcrypt($data['password'])
                        ]);

        if($request->role != ''){
          $newUser->assignRole($request->role);
        }
        
        if($request->branches != ''){
          $newUser->branches()->attach($request->branches);
        }

        if($request->hasFile('avatar')){
          $ext = $request->file('avatar')->getClientOriginalExtension();

          if(in_array(Str::lower($ext), getRestrictedExt())){
            return "FORBIDDEN";
          }
          
          $name = Str::slug($newUser->name).'_'.round(microtime(true)).'.'.$ext;

          $request->file('avatar')->move('img/users/', $name);
          $newUser->avatar = $name;
          $newUser->save();
        }

        return redirect('/administrator/users')->with('sukses', 'Create User Success.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $roles = $user->roles;
        $branches = GlbBranch::with('company')->where('CB_IsActive', true)->get();
        
        return view('admin.users.create-edit', compact(['user', 'roles', 'branches']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        if($user->hasRole('super-admin')){
          $roles = Role::all();
        } else {
          $roles = Role::where('name', '<>', 'super-admin')->get();
        }
               
        $branches = GlbBranch::with('company')->where('CB_IsActive', true)->get();
        
        return view('admin.users.create-edit', compact(['user', 'roles', 'branches']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
                                'name' => 'required',
                                'username' => [
                                  'required',
                                  Rule::unique('users')->ignore($user)
                                ],
                                'email' => [
                                  'required',
                                  Rule::unique('users')->ignore($user)
                                ],         
                              ]);
        
        if($validator->fails()){
          return redirect('/administrator/users/'.$user->id.'/edit')->withErrors($validator);
        }

        $user->update([
                        'email' => $request->email,
                        'username' => $request->username,
                        'name' => Crypt::encrypt($request->name),
                      ]);
                      
        if($request->password != ''){
          $user->password = bcrypt($request->password);
          $user->save();
        }

        if($request->role != ''){
          if(!auth()->user()->hasRole('super-admin') && in_array('super-admin', $request->role)){
            return redirect()->back()->with('gagal', 'You are not authorized to assign this role');
          }
          $user->syncRoles($request->role);
        } else {
          $user->detachRoles();
        }

        if($request->branches != ''){
          $user->branches()->sync($request->branches);
        } else {
          $user->branches()->detach();
        }

        if($request->hasFile('avatar')){
          $ext = $request->file('avatar')->getClientOriginalExtension();

          if(in_array(Str::lower($ext), getRestrictedExt())){
            return "FORBIDDEN";
          }

          $fileLama = public_path().'/img/users/'.$user->avatar;

          if(!is_dir($fileLama) && file_exists($fileLama)){
            unlink($fileLama);
          }
          
          $name = Str::slug($user->name).'_'.round(microtime(true)).'.'.$ext;
          $request->file('avatar')->move('img/users/', $name);
          $user->avatar = $name;
          $user->save();
        }

        return redirect('/administrator/users/'.$user->id.'/edit')->with('sukses', 'Update User Success.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->hasRole('super-admin')){
          return redirect()->back()->with('gagal', 'You cant remove a super admin User.');
        }
        $user->roles()->detach();
        $user->permissions()->detach();
        $user->branches()->detach();
        $user->delete();

        return redirect('/administrator/users')->with('sukses', 'Delete User Success.');
    }
}
