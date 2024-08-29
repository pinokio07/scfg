<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlbCompany;
use App\Models\AccGlAccount;
use Str;

class AdminCompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = GlbCompany::get([
                             'id',
                             'GC_IsActive',
                             'GC_Name as Company Name',
                             'GC_Address1 as Address',
                             'GC_RN_NKCountryCode as Country',
                             'GC_City as City'
                           ]);

        return view('admin.companies.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company = new GlbCompany;
        $disabled = 'false';

        return view('admin.companies.create-edit', compact(['company', 'disabled']));
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
          'GC_Name' => 'required',
          'GC_Address1' => 'required',
          'GC_RN_NKCountryCode' => 'required',
          'GC_City' => 'required',
          'GC_PostCode' => 'required',
          'GC_Phone' => 'required',
          'GC_RX_NKLocalCurrency' => 'required'
        ]);

        if($data){
          $company = GlbCompany::create($request->all());
          $company->GC_IsActive = true;

          if($request->hasFile('GC_Logo')){
            $ext = $request->file('GC_Logo')->getClientOriginalExtension();

            if(in_array(Str::lower($ext), getRestrictedExt())){
              return "FORBIDDEN";
            }

            $name = Str::slug($company->GC_Code).'_'.round(microtime(true)).'.'.$ext;
            $request->file('GC_Logo')->move('img/companies/', $name);
            $company->GC_Logo = $name;
          }
          $company->save();

          return redirect('/administrator/companies')->with('sukses', 'Create Company Success');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(GlbCompany $company)
    {
        $disabled = 'disabled';

        return view('admin.companies.create-edit', compact(['company', 'disabled']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(GlbCompany $company)
    {
        $disabled = 'false';

        return view('admin.companies.create-edit', compact(['company', 'disabled']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GlbCompany $company)
    {
        $data = $request->validate([
          'GC_Name' => 'required',
          'GC_Address1' => 'required',
          'GC_RN_NKCountryCode' => 'required',
          'GC_City' => 'required',
          'GC_PostCode' => 'required',
          'GC_Phone' => 'required',
          'GC_RX_NKLocalCurrency' => 'required'
        ]);

        if($data){
          $company->update($request->all());

          if($request->hasFile('GC_Logo')){

            $ext = $request->file('GC_Logo')->getClientOriginalExtension();

            if(in_array(Str::lower($ext), getRestrictedExt())){
              return "FORBIDDEN";
            }

            $fileLama = public_path().'/img/companies/'.$company->GC_Logo;
            if(!is_dir($fileLama) && file_exists($fileLama)){
              unlink($fileLama);
            }

            $name = Str::slug($company->GC_Code).'_'.round(microtime(true)).'.'.$ext;
            $request->file('GC_Logo')->move('img/companies/', $name);
            $company->GC_Logo = $name;
          }

          $company->save();

          return redirect('/administrator/companies/'.$company->id.'/edit')->with('sukses', 'Update Company Success');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GlbCompany $company)
    {
        $company->delete();

        return redirect('/administrator/companies')->with('sukses', 'Delete Company Success');
    }

    public function select2(Request $request)
    {
        $data = [];

          if($request->has('q') && $request->q != ''){
              $search = $request->q;
              $data = GlbCompany::select("id","GC_Name")
                                  ->where('GC_IsActive', true)
                                  ->where('GC_Name','LIKE',"%$search%")
                                  ->get();
          }

          return response()->json($data);
    }
    
}
