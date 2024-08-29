<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\HouseDetail;
use DB, Auth, Crypt, DataTables;

class ManifestHouseDetailsController extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = HouseDetail::where('HouseID', $request->id);

          return DataTables::eloquent($query)
                           ->addIndexColumn()
                           ->addColumn('actions', function($row){
                            $btn = '';

                            if(auth()->user()->can('edit_manifest_consolidations')){

                            $btn = '<button type="button"
                                            data-toggle="modal"
                                            data-target="#modal-item"
                                            class="btn btn-xs btn-warning elevation-2 mr-1 editDetail"
                                            data-house="'.Crypt::encrypt($row->HouseID).'"
                                            data-id="'.$row->id.'"
                                            data-hs="'.$row->HS_CODE.'"
                                            data-desc="'.$row->UR_BRG.'"
                                            data-cif="'.$row->CIF.'"
                                            data-fob="'.$row->FOB.'"
                                            data-bm="'.$row->BM_TRF.'"
                                            data-ppn="'.$row->PPN_TRF.'"
                                            data-pph="'.$row->PPH_TRF.'">
                                      <i class="fas fa-edit"></i>
                                    </button>';
                              if(auth()->user()->can('delete_manifest_consolidations|delete_manifest_shipments')){
                                $btn .= '<button class="btn btn-xs btn-danger elevation-2 hapusDetail"
                                            data-href="'. route('house-details.destroy', ['house_detail' => $row->id]) .'">
                                          <i class="fas fa-trash"></i>
                                        </button>';
                              }
                            }

                            return $btn;
                           })
                           ->rawColumns(['actions'])
                           ->toJson();
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->cannot('edit_manifest_consolidations') 
            && Auth::user()->cannot('edit_manifest_shipments')){
          if($request->ajax()){
            return response()->json(['status' => 'Failed', 'message' => 'You are not authorized to edit this data.']);
          }
          return abort(403);
        }
        $data = $this->getValidated();

        if($data){
          DB::beginTransaction();
          
          $house = House::findOrFail(Crypt::decrypt($request->house_id));

          try {
            
            $hasil = array_merge($data, ['HouseID' => $house->id]);
            $house_detail = HouseDetail::create($hasil);

            DB::commit();

            createLog('App\Models\HouseDetail', $house_detail->id, 'Create House Item '. $house_detail->HS_CODE);

            DB::commit();

            if($request->ajax()){
              return response()->json([
                'status' => 'OK',
                'house' => $house->id,
                'message' => 'Create House Item Success.'
              ]);
            }
            return redirect(url()->previous().'/edit')->with('sukses', 'Update House Items Success.');

          } catch (\Throwable $th) {
            DB::rollback();

            if($request->ajax()){
              return response()->json(['status' => 'FAILED', 'message' => $th->getMessage()]);
            }
            
            throw $th;
          }
          

        }
    }

    public function update(Request $request, HouseDetail $house_detail)
    {
        if(Auth::user()->cannot('edit_manifest_consolidations') 
            && Auth::user()->cannot('edit_manifest_shipments')){
          if($request->ajax()){
            return response()->json(['status' => 'Failed', 'message' => 'You are not authorized to edit this data.']);
          }
          return abort(403);
        }

        $data = $this->getValidated();

        if($data){
          DB::beginTransaction();
          
          try {
            $house_detail->update($data);

            DB::commit();

            if(!empty($house_detail->getChanges())){
              $info = 'Update House Items '.$house_detail->HS_CODE.' <br> <ul>';

              foreach ($house_detail->getChanges() as $key => $value) {
                if($key != 'updated_at'){
                  $info .= '<li> Update ' . $key . ' to ' . $value .'</li>';
                }
              }

              $info .= '</ul>';

              createLog('App\Models\HouseDetail', $house_detail->id, $info);

              DB::commit();
            }

            if($request->ajax()){
              return response()->json([
                'status' => 'OK',
                'house' => $house_detail->HouseID,
                'message' => 'Update House Item Success.'
              ]);
            }
           
            return redirect(url()->previous().'/edit')->with('sukses', 'Update House Item Success.');

          } catch (\Throwable $th) {
            DB::rollback();

            if($request->ajax()){
              return response()->json(['status' => 'FAILED', 'message' => $th->getMessage()]);
            }

            throw $th;
            
          }
          

        }
    }    
    public function destroy(Request $request, HouseDetail $house_detail)
    {
        if(Auth::user()->cannot('edit_manifest_consolidations') 
            && Auth::user()->cannot('edit_manifest_shipments')){
          return abort(403);
        }

        DB::beginTransaction();

        try {
          $house = $house_detail->HouseID;
          $hid = $house_detail->id;
          $houseItem = $house_detail->UR_BRG ?? $house_detail->HS_CODE;

          $house_detail->delete();

          createLog('App\Models\HouseDetail', $hid, 'Delete House Item '.$houseItem);

          DB::commit();
          
          if($request->ajax()){
            return response()->json(['status' => 'OK', 'house' => $house]);
          }          

        } catch (\Throwable $th) {
          DB::rollback();

          if($request->ajax()){
            return response()->json(['status' => 'FAILED', 'message' => $th->getMessage()]);
          }
          
        }
    }

    public function getValidated()
    {
      return request()->validate([
        'HS_CODE' => 'required',
        'UR_BRG' => 'nullable',
        'CIF' => 'nullable|numeric',
        'FOB' => 'nullable|numeric',
        'BM_TRF' => 'nullable|numeric',
        'PPN_TRF' => 'nullable|numeric',
        'PPH_TRF' => 'nullable|numeric',
      ]);
    }
}
