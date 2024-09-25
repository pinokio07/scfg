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
                          ->editColumn('TGL_SKEP', function($row){
                              return ($row->TGL_SKEP && $row->TGL_SKEP->year > 1) ? $row->TGL_SKEP->format('d-M-Y') : "-";
                            })
                          ->addColumn('actions', function($row){
                            $btn = '';

                            if(auth()->user()->can('edit_manifest_consolidations')){

                            $btn = '<button type="button"
                                            data-toggle="modal"
                                            data-target="#modal-item"
                                            class="btn btn-xs btn-warning elevation-2 mr-1 editDetail"
                                            data-house="'.Crypt::encrypt($row->HouseID).'"
                                            data-id="'.$row->id.'">
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

          $house->refresh();

          $this->calculatehs($house);
          
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

    public function show(Request $request, HouseDetail $house_detail)
    {
      if($request->ajax()){
        return response()->json([
          'status' => 'OK',
          'detail' => $house_detail
        ]);
      }

      return $house_detail;
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
            $house = $house_detail->house;
            
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

            $house->refresh();

            $this->calculatehs($house);

            DB::commit();

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
          $house = $house_detail->house;
          $hid = $house_detail->id;
          $houseItem = $house_detail->UR_BRG ?? $house_detail->HS_CODE;

          $house_detail->delete();

          createLog('App\Models\HouseDetail', $hid, 'Delete House Item '.$houseItem);

          DB::commit();

          $house->refresh();

          $estBM = $house->details()->sum('BEstimatedBM');
          $estPPN = $house->details()->sum('BEstimatedPPN');
          $estPPH = $house->details()->sum('BEstimatedPPH');
          $estBMTP = $house->details()->sum('BEstimatedBMTP');

          $house->update([
            'HEstimatedPPN' => $estPPN,
            'HEstimatedPPH' => $estPPH,
            'HEstimatedBM' => $estBM,
            'HEstimatedBMTP' => $estBMTP,
          ]);

          DB::commit();
          
          if($request->ajax()){
            return response()->json(['status' => 'OK', 'house' => $house->id]);
          }          

        } catch (\Throwable $th) {
          DB::rollback();

          if($request->ajax()){
            return response()->json(['status' => 'FAILED', 'message' => $th->getMessage()]);
          }
          
        }
    }

    public function select2(House $house, Request $request)
    {
      $data = [
        'BM_TRF' => 0,        
        'PPN_TRF' => 0,        
        'PPH_TRF' => 0,
        'BMTP_TRF' => 0,
      ];

      $fhs = ['87116092', '87116093', '87116094', '87116095', '87116099'];
      $subhs = ['8712', '9102', '9101', '4202', '3303', '3304', '3305', '3306', '3307'];
      $minhs = ['61','62','63','64','73'];
      $cif3 = ['4901','4902','4903','4904'];
      $cif57 = ['57'];

      if($request->has('val') && $request->val != '')
      {
        $fob = $house->fob_cal;

        $hs = $request->val;
        $shs = substr($hs,0,4);
        $mhs = substr($hs,0,2);

        $BM_TRF = 0;
        $BMTP_TRF = 0;
        $PPN_TRF = 0;        
        $PPH_TRF = 0;
        
        if($fob <= 3)
        {
          if( !in_array($hs, $cif3) )
          {       
            $PPN_TRF = 11;
          }
        } elseif($fob > 3 && $fob <= 1500) {
          if(in_array($hs, $fhs)
                    || in_array($shs, $subhs)
                    || in_array($mhs, $minhs))
          {
            $BM_TRF = RefTarifBM::where('HSCode', $hs)->first()->BM ?? 0;
            $BMTP_TRF = RefTarifBMTP::where('HSCode', $hs)->first()->Tarif3 ?? 0;
            $PPN_TRF = 11;
            $PPH_TRF = RefTarifPPH::where('HSCode', $hs)->first()->PPH ?? 0;
            
            if ($house->JNS_ID_PENERIMA !== "5") {
              $PPH_TRF = $PPH_TRF*2;
            }
            
          }elseif(in_array($mhs, $cif57))
          {
            $BM_TRF = 7.5;
            $BMTP_TRF = 78027;
            $PPN_TRF = 11;        
          } else {
            $BM_TRF = 7.5;
            $PPN_TRF = 11;         
          }
        }        

        $data = [
          'BM_TRF' => $BM_TRF,        
          'PPN_TRF' => $PPN_TRF,        
          'PPH_TRF' => $PPH_TRF,
          'BMTP_TRF' => $BMTP_TRF,
        ];
      }

      return response()->json($data);
    }

    public function calculatehs(House $house)
    {
          $HTotalPPN = 0;
          $HTotalPPH = 0;
          $HTotalBM = 0;
          $HTotalBMTP = 0;
          // $totalCIF = 0;

        foreach($house->details as $detail)
        {
          $JML_SAT_HRG = number_format($detail->JML_SAT_HRG, 2, '.', '');
          $PPN_TRF = number_format($detail->PPN_TRF, 2, '.', '');
          $PPH_TRF = number_format($detail->PPH_TRF, 2, '.', '');
          $BM_TRF = number_format($detail->BM_TRF, 2, '.', '');
          $BMTP_TRF= number_format($detail->BMTP_TRF, 2, '.', '');
          $CIF = number_format($detail->CIF, 2, '.', '');
          $CIFHITUNG = $house->KD_VAL === "IDR"? $CIF: $CIF*$house->NDPBM;
          $BMTP = roundUp((($BMTP_TRF*$JML_SAT_HRG)),1);
          $BM = round((($CIFHITUNG*($BM_TRF/100))),2);            
          $NilaiImport = ($BM)+($CIFHITUNG)+$BMTP; 

          $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
          $PPN= floor(($NilaiImport*($PPN_TRF/100))); 
          $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));

          $detail->update([
            'BEstimatedBMTP' => $BMTP,
            'BEstimatedPPN' => $PPN,
            'BEstimatedPPH' => $PPH,
            'BEstimatedBM' => $BM,
          ]);

          $HTotalPPN += $PPN;
          $HTotalPPH += $PPH;
          $HTotalBM += $BM;
          $HTotalBMTP += $BMTP;
          // $totalCIF += $CIF;
        }

        DB::commit();

        $house->update([
          'HEstimatedPPN'  => floor($HTotalPPN),
          'HEstimatedBM'   => roundUp($HTotalBM,1000),
          'HEstimatedPPH'  => floor($HTotalPPH),
          'HEstimatedBMTP'  => roundUp($HTotalBMTP,1000),
          // 'CIF' => $totalCIF
        ]);

        DB::commit();
    }

    public function getValidated()
    {
      return request()->validate([
        'HS_CODE' => 'required',
        'UR_BRG' => 'nullable',
        'FL_BEBAS' => 'nullable',
        'NO_SKEP' => 'nullable',
        'TGL_SKEP' => 'nullable',
        'IMEI1' => 'nullable',
        'IMEI2' => 'nullable',
        'CIF' => 'nullable|numeric',
        'FOB' => 'nullable|numeric',
        'JML_SAT_HRG' => 'nullable|numeric',
        'KD_SAT_HRG' => 'nullable|string',
        'JML_KMS' => 'nullable|numeric',
        'JNS_KMS' => 'nullable|string',
        'BM_TRF' => 'nullable|numeric',
        'BMTP_TRF' => 'nullable|numeric',
        'PPN_TRF' => 'nullable|numeric',
        'PPH_TRF' => 'nullable|numeric',
      ]);
    }
}
