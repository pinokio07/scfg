<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefCustomsOffice;
use DataTables;

class SetupCustomsOfficesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefCustomsOffice::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                          
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'Kdkpbc' => 'KPBC Code',
          'UrKdkpbc' => 'Nama KPBC',
          'Kota' => 'Kota',
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      return redirect('/setup/customs-offices');
    }

    public function upload()
    {
      return redirect('/setup/customs-offices');
    }

    public function select2(Request $request)
    {
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefCustomsOffice::where(function($query) use($search){
                                        $query->where('Kdkpbc','LIKE',"%$search%")
                                              ->orWhere('UrKdkpbc','LIKE',"%$search%")
                                              ->orWhere('Kota','LIKE',"%$search%");
                                      })
                                      ->get();
        }

        return response()->json($data);
    }
}
