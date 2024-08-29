<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlbCompany;
use App\Models\GlbBranch;

class AdminCompaniesBranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GlbCompany $company)
    {
        $items = GlbBranch::where('company_id', $company->id)
                          ->get([
                            'id',
                            'CB_Code as Branch Code',
                            'CB_FullName as Branch Name',
                            'CB_Address as Branch Address',
                            'CB_Phone as Branch Phone',
                            'CB_City as Branch City'
                          ]);

        return view('admin.companies.index-branches', compact(['company', 'items']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(GlbCompany $company)
    {
        $branch = new GlbBranch;
        $disabled = 'false';

        return view('admin.companies.create-edit-branch', compact(['company', 'branch', 'disabled']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GlbCompany $company, Request $request)
    {
        $data = $request->validate([
          'CB_Code' => 'required',
          'CB_FullName' => 'required',
          'CB_Address' => 'required'
        ]);

        if($data){
          $branch = $company->branches()->create($request->all());
          $branch->CB_IsActive = true;
          $branch->save();

          return redirect('/administrator/companies/'.$company->id.'/branches')->with('sukses', 'Create Branch Success.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(GlbCompany $company, GlbBranch $branch)
    {
        $disabled = 'disabled';

        return view('admin.companies.create-edit-branch', compact(['company', 'branch', 'disabled']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(GlbCompany $company, GlbBranch $branch)
    {
        $disabled = 'false';

        return view('admin.companies.create-edit-branch', compact(['company', 'branch', 'disabled']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GlbCompany $company, GlbBranch $branch)
    {
        $data = $request->validate([
          'CB_Code' => 'required',
          'CB_FullName' => 'required',
          'CB_Address' => 'required'
        ]);

        if($data){
          $branch->update($request->all());

          return redirect('/administrator/companies/'.$company->id.'/branches')->with('sukses', 'Update Branch Success.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GlbCompany $company, $id)
    {
        $company->branches()->findOrFail($id)->delete();

        return redirect('/administrator/companies/'.$company->id.'/branches')->with('sukses', 'Delete Branch Success.');
    }

    public function select2(Request $request)
    {
      $cid = $request->id;

      $results = GlbBranch::where('CB_IsActive', true)
                         ->where('company_id', $cid)
                         ->get();
      $output = '';

      foreach ($results as $key => $result) {
        $output .= '<option value="'.$result->id.'">'.$result->CB_Code .' - '.$result->CB_FullName.'</option>';
      }

      echo $output;
    }

    public function selectBranch(Request $request)
    {
        $data = [];
        $company = activeCompany()->company;

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = GlbBranch::with('company')
                                ->where('CB_IsActive', true)
                                ->where('company_id', $company->id)
                                ->where(function($query) use($search){
                                  $query->where('CB_FullName','LIKE',"%$search%")
                                        ->orWhere('CB_Code','LIKE',"%$search%");
                                })
                                ->limit(10)
                                ->get();
        }

        return response()->json($data);
    }
    public function currentBranch(Request $request)
    {
        $data = [];
        $company = activeCompany()->company;

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = GlbBranch::with('company')
                              // ->select("id","CB_Code","CB_FullName","company")
                              ->where('company_id', $company->id)
                              ->where('CB_IsActive', true)
                              ->where(function($query) use($search){
                                $query->where('CB_FullName','LIKE',"%$search%")
                                      ->orWhere('CB_Code','LIKE',"%$search%");
                              })
                              ->limit(10)
                              ->get();
        }

        return response()->json($data);
    }
}
