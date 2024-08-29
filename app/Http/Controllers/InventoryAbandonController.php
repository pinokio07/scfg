<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use Carbon\Carbon;
use DataTables, Crypt;

class InventoryAbandonController extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $tanggal = today()->subDays(30)->format('Y-m-d');

          $query = House::with(['master', 'details'])                      
                        ->whereNull('ExitDate')
                        ->whereNotNull('SCAN_IN_DATE')
                        ->where('SCAN_IN_DATE', '<', $tanggal);

          return DataTables::eloquent($query)
                          ->addIndexColumn()                         
                          ->addColumn('NO_PLP', function($row){
                            return $row->master->PLPNumber ?? "-";
                          })
                          ->addColumn('TGL_PLP', function($row){
                            $tglplp = $row->master->PLPDate;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('d/m/Y');
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
                          ->editColumn('NO_HOUSE_BLAWB', function($row){
                            $btn = '<a href="'.route('manifest.shipments.show', ['shipment' => Crypt::encrypt($row->id)]).'">'.$row->NO_HOUSE_BLAWB.'</a>';

                            return $btn;
                          })
                          ->addColumn('AGE', function($row){
                            $diff = 0;
                            if($row->SCAN_IN_DATE){
                              $lama = Carbon::parse($row->SCAN_IN_DATE);

                              $diff = $lama->diffInDays(today());
                            }

                            return $diff;
                          })
                          ->rawColumns(['NO_HOUSE_BLAWB'])
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'TGL_HOUSE_BLAWB' => 'Tgl HAWB',
          'NO_HOUSE_BLAWB' => 'No HAWB',
          'NO_PLP' => 'Nomor PLP',
          'TGL_PLP' => 'Tanggal PLP',
          'SCAN_IN_DATE' => 'Tanggal Masuk Gudang',
          'BC_CODE' => 'Kode BC',
          'BC_DATE' => 'Tanggal BC 11',
          'BC_STATUS' => 'BC Status',
          'NM_PENGIRIM' => 'Nama Pengirim',
          'NM_PENERIMA' => 'Consignee',
          'AL_PENERIMA' => 'Alamat',
          'LM_TRACKING' => 'LM Tracking',
          'AGE' => 'Age',
        ]);

        return view('pages.beacukai.abandon', compact('items'));
    }
}
