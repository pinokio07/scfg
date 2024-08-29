<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use Carbon\Carbon;
use DataTables, Crypt, Auth, DB;

class InventoryCurrentNowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $tanggal = today();
          if($request->tanggal){
            $tanggal = Carbon::createFromFormat('d-m-Y', $request->tanggal);
          }
          
          $query = House::with(['master', 'details', 'activeTegah'])
                        ->where(function($ex) use ($tanggal){
                          $ex->where('ExitDate', '>', $tanggal)
                            ->orWhereNull('ExitDate');
                        })
                        ->whereNotNull('SCAN_IN_DATE')
                        ->where('SCAN_IN_DATE', '<=', $tanggal);

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
                          ->editColumn('TGL_BC11', function($row){
                            $tglplp = $row->TGL_BC11;
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
                          ->addColumn('NO_SEGEL', function($row){
                            return $row->SEAL_NO ?? $row->master->NO_SEGEL;
                           })
                          ->editColumn('NO_SPPB', function($row){
                            return $row->SPPBNumber ?? $row->NO_SPPB ?? "-";
                           })
                          ->editColumn('TGL_SPPB', function($row){
                            $tglplp = $row->SPPBDate ?? $row->TGL_SPPB;
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
                          ->editColumn('NO_MASTER_BLAWB', function($row){  
                            return $row->mawb_parse;
                          })
                          ->addColumn('UR_BRG', function($row){
                            $brg = '';
                            $count = $row->details->count();

                            if($count > 0){
                              foreach ($row->details as $key => $detail) {
                                $brg .= $detail->UR_BRG;
                                (($key + 1) < $count) ? $brg .= ', ' : '';
                              }
                            }

                            return $brg;
                          })
                          ->addColumn('Status', function($row) use ($tanggal){
                            $dateIn = Carbon::parse($row->SCAN_IN_DATE);

                            return ($dateIn->diffInDays($tanggal, false) > 30) 
                                      ? 'Abandon' : 'Current Now';
                          })
                          ->addColumn('Keterangan', function($row){
                            return ($row->activeTegah->isNotEmpty()) ? "RESTRICTED" : "";
                          })
                          ->rawColumns(['NO_HOUSE_BLAWB'])
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'NM_PEMBERITAHU' => 'Nama Pemberitahu',
          'NO_BC11' => 'Nomor BC 11',
          'TGL_BC11' => 'Tanggal BC 11',
          'NO_POS_BC11' => 'Pos',
          'NO_FLIGHT' => 'Sarana Pengangkut',
          'NO_PLP' => 'Nomor PLP',
          'TGL_PLP' => 'Tanggal PLP',
          'NO_SEGEL' => 'Segel',
          'JML_BRG' => 'Jumlah Koli',
          'BRUTO' => 'Bruto',
          'NO_MASTER_BLAWB' => 'MAWB',
          'NO_HOUSE_BLAWB' => 'HAWB',
          'UR_BRG' => 'Uraian Barang',
          'NM_PENERIMA' => 'Consignee',
          'AL_PENERIMA' => 'Alamat',
          'NO_SPPB' => 'Nomor SPPB',
          'TGL_SPPB' => 'Tanggal SPPB',
          'Status' => 'Status',
          'SCAN_IN_DATE' => 'Tanggal dan Waktu Masuk TPS',
          'Keterangan' => 'Keterangan',
        ]);

        return view('pages.beacukai.currentnow', compact(['items']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
