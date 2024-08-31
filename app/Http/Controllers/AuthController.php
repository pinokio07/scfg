<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PassLog;
use App\Models\UserLoginLog;
use Crypt, Auth, Str, DB, DataTables;

class AuthController extends Controller
{
    public function index()
    {
      if(Auth::check()){
        //If Authenticated redirect to Dashboard
        return redirect('/dashboard');
      }
      //Return to Welcome page
      return view('welcome');
    }

    public function postlogin(Request $request)
    {
        //Get Request Parameters
        $username = $request->email;
        $password = base64_decode($request->password);
    
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
          //If Username is Email
          Auth::attempt(['email' => $username, 'password' => $password]);
        } else {
          //If Username is Not Email
          Auth::attempt(['username' => $username, 'password' => $password]);
        }

        if(Auth::check()){
          // Get Auth User
          $user = Auth::user();
          //Update timestamps
          $user->touch();

          if($user->branches->isEmpty()){
            Auth::logout();
  
            return redirect('/')->with('gagal', 'You dont have a branch.');
          }

          //Check Branch
          if( $user->branches->count() > 1
              // && !$user->hasRole('super-admin')
          ){
            return redirect('/active-company');
          // } elseif(!$user->hasRole('super-admin')) {
          } else {
            $branch = $user->branches->first()->id;
            $user->branches()->updateExistingPivot($branch, ['active' => true]);
            session(['brid' => $branch]);
            // return redirect()->intended('/dashboard');
          }   

          DB::beginTransaction();

          try {
            $user->loginLogs()->create(['type' => 'Login']);
            DB::commit();
          } catch (\Throwable $th) {
            //throw $th;
          }
          
          //If User monitoring
          if($user->hasExactRoles('monitoring')){
            return redirect('/monitoring');
          }
          //Return to Intended URL
          return redirect()->intended('/dashboard');
          
        }
        //Return to login page with Errors
        return redirect('/')->with('gagal', 'Your Credential not found.');

    }

    public function profile()
    {
        $user = Auth::user();

        if($user->hasRole('super-admin')){
          $roles = Role::all();
        } else {
          $roles = Role::where('name', '<>', 'super-admin')->get();
        }      

        return view('pages.profile', compact(['user', 'roles']));
    }

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

      ($user->hasRole('super-admin')) ? $redirBack = '/administrator/profile' : $redirBack = '/profile';

      if($validator->fails()){
        return redirect($redirBack)->withErrors($validator);
      }

      if($user->id != Auth::id()){
        return redirect($redirBack)->with('gagal', 'You are not Authorize to Edit this Account.');
      }

      if($user->cannot('bypass-password')){
        if($request->password != ''){
          //Find 5 Latest Password
          $pass = $request->password;
          $used = 0;
  
          $check = PassLog::where('user_id', $user->id)
                          ->latest()
                          ->take(5)
                          ->get();
  
          foreach ($check as $cek) {
            $passCheck = Crypt::decrypt($cek->pass);
            if($passCheck == $pass){
              $used++;
            }
          }
  
          if($used > 0){
            return redirect($redirBack)->with('gagal', 'Please insert Password that is not already used.');
          }
        }
      }      
      
      DB::beginTransaction();

      try {
        $user->update([
          'email' => $request->email,
          'username' => $request->username,
          'name' => Crypt::encrypt($request->name),
        ]);

        if($request->password != ''){          
          $user->update(['password' => bcrypt($request->password)]);
  
          $user->passLog()->create([
            'pass' => Crypt::encrypt($request->password)
          ]);
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
          $user->update(['avatar' => $name]);  
        }

        DB::commit();

        return redirect($redirBack)->with('sukses', 'Edit Profile Success');

      } catch (\Throwable $th) {
        DB::rollback();
        throw $th;
      }      
      
    }

    public function logs()
    {
        $uid = Auth::id();
        // $logs = $user->load(['loginLogs']);

        // $output = '<tr>';

        // foreach ($logs as $key => $logs) {
        //   $output .= '<td>'.$logs->created_at->format('d/M/Y H:i:s').'</td>'
        //               . '<td>'.$logs->type.'</td>';
        // }

        // $output .= '</tr>';

        // echo $output;

        $query = UserLoginLog::where('user_id', $uid);

        return DataTables::eloquent($query)
                         ->addIndexColumn()
                         ->editColumn('created_at', function($row){
                            $created = $row->created_at;
                            if($created){
                              $time = Carbon::parse($created);
                              $display = $time->format('d/M/Y H:i:s');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }

                            $show = [
                              'display' => $display,
                              'timestamp' => $timestamp
                            ];

                            return $show; 
                         })
                         ->toJson();
    }

    public function logout()
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
          $user->loginLogs()->create(['type' => 'Logout']);
          DB::commit();
        } catch (\Throwable $th) {
          //throw $th;
        }

        Auth::logout();

        return redirect('/')->with('sukses', 'Logout success.');  
    }
}
