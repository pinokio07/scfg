<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlbCompany;

class AdminCompaniesUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GlbCompany $company)
    {
        $company->load('users');

        return view('admin.companies.users', compact('company'));
    }

    public function update(Request $request, GlbCompany $company)
    {
        //
    }
    
    public function select()
    {
        $user = auth()->user();
        $branches = $user->branches;

        return view('pages.company',compact(['user', 'branches']));
    }

    public function set(Request $request)
    {
        $user = auth()->user();
        $user->branches()
              ->newPivotStatement()
              ->where('user_id', '=', $user->id)
              ->update(array('active' => 0));
        $user->save();

        $user->branches()->updateExistingPivot($request->branch_id,
          ['active' => true,]
        );

        return redirect('/dashboard');
    }
}
