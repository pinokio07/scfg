<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Barkir;
use App\Helpers\Ceisa40;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\Tariff;
use App\Models\KodeDok;
use App\Models\ShipmentsJobHeader;
use App\Models\User;
use Carbon\Carbon;
use DataTables, Auth, Crypt, Str, DB, PDF;

class ManifestShipmentsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = activeCompany();
        if($request->ajax()){
          $query = House::with(['master'])                        
                        ->where('BRANCH', $company->id);
                        
          if($request->has('search') && $request->search['value'] != '')
          {
            $search = Str::replace([' ', '-'], '', $request->search['value']);

            $query->where(function($h) use ($search){
              $h->whereHas('master', function($m) use ($search){
                  return $m->where('MAWBNumber', 'LIKE', "%$search%");
                })
                // ->orWhere("NO_BARANG", 'LIKE', "%{$search}%");
                ->orWhereRaw("REPLACE(NO_BARANG, '-', '') LIKE '%$search%' ");
            });            
          }

          if($request->order[0]['column'] == 0)
          {
            $query->latest('TGL_TIBA')->latest('JAM_TIBA');
          }

          return DataTables::eloquent($query)
                           ->addIndexColumn()
                           ->editColumn('NO_BARANG', function($row) use ($user){
                            
                            $hawb = $row->NO_BARANG;

                            if($user->can('edit_manifest_shipments'))
                            {
                              $url = route('manifest.shipments.edit', ['shipment' => Crypt::encrypt($row->id)]);
                            } else {                              
                              $url = route('manifest.shipments.show', ['shipment' => Crypt::encrypt($row->id)]);
                            }

                            $show = [
                              'url' => $url,
                              'raw' => $hawb,
                              'filter' => \Str::replace([' ', '-'], '', $hawb)
                            ];

                            return $show;
                           })
                           ->editColumn('TGL_TIBA', function($row){
                              if($row->TGL_TIBA){
                                $time = Carbon::parse($row->TGL_TIBA);
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
                           ->editColumn('SCAN_IN_DATE', function($row){
                              if($row->SCAN_IN_DATE){
                                $time = Carbon::parse($row->SCAN_IN_DATE);
                                $display = $time->format('d/m/Y H:i:s');
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
                           ->editColumn('ExitDate', function($row){
                              if($row->ExitDate){
                                $time = Carbon::parse($row->ExitDate);
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
                           ->editColumn('JAM_TIBA', function($row){
                              return $row->JAM_TIBA;
                           })
                           ->editColumn('NO_MASTER_BLAWB', function($row){
                            $mawb = $row->mawb_parse;

                            $show = [
                              'display' => $mawb,
                              'raw' => $row->NO_MASTER_BLAWB
                            ];

                            return $show;
                           })                           
                           ->rawColumns(['NO_BARANG'])
                           ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'NM_PEMBERITAHU' => 'Nama Pemberitahu',
          'NO_MASTER_BLAWB' => 'No Master BLAWB',
          'NO_BARANG' => 'No Barang',
          'NM_PENERIMA' => 'Nama Penerima',
          'AL_PENERIMA' => 'Alamat Penerima',
          'TGL_TIBA' => 'Tanggal Tiba',
          'JAM_TIBA' => 'Jam Tiba',
          'SCAN_IN_DATE' => 'Masuk Gudang',
          'ExitDate' => 'Exit Date',
          'ExitTime' => 'Exit Time',
          'TPS_GateInREF' => 'Gate In Ref',
          'TPS_GateOutREF' => 'Gate Out Ref',
        ]);

        return view('pages.manifest.shipments.index', compact(['items']));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        
        $ids = $request->ids;
        try {
          if($request->has('ceisa') && $request->ceisa > 0)
          {
            $ceisa = new Ceisa40;

            $res = $ceisa->tarikRespon($ids, 'hawb');
            
            if($res['status'] != 'OK')
            {
              return response()->json([
                'status' => $res['status'],
                'message' => $res['message']
              ]);
            }

            return response()->json([
              'status' => 'OK',
              'message' => 'Tarik response berhasil'
            ]);
          }
          $barkir = new Barkir;
          $respon = $barkir->mintarespon($ids, 1, 0, true);

          return response()->json([
            'status' => $respon['status'],
            'message' => $respon['message']
          ]);
        } catch (\Throwable $th) {
          //throw $th;
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
        }
    }

    public function show(House $shipment)
    {
        $user = \Auth::user();
        $item = $shipment->load(['details', 'master.houses', 'branch']);

        if($user->can('multi_tenant')            
            || $user->can('create_accounting_billing_cost')
            || $user->can('create_accounting_billing_revenue'))
        {
          $brs = $user->branches->pluck('id')->toArray();

          if(!in_array($item->BRANCH, $brs)) {
            return abort(403);
          }
        } elseif($user->activeCompany()->company_id != $item->branch->company_id
                  || $user->activeCompany()->id != $item->BRANCH)
        {
          return abort(403);
        }

        $disabled = 'disabled';

        if(auth()->user()->can('edit_manifest_shipments')){
          $disabled = false;
        }

        $headerHouse = $this->headerHouse();
        $headerDetail = $this->headerHouseDetail();
        $tariff = Tariff::all();
        $kodeDocs = KodeDok::all();

        return view('pages.manifest.shipments.create-edit', compact(['item', 'headerHouse', 'headerDetail', 'tariff', 'disabled', 'kodeDocs']));
    }

    public function edit(House $shipment)
    {
        $user = \Auth::user();
        $item = $shipment->load(['details', 'master.houses']);
        $disabled = 'disabled';

        if($user->can('multi_tenant')        
          || $user->can('create_accounting_billing_cost')
          || $user->can('create_accounting_billing_revenue'))
        {
          $brs = $user->branches->pluck('id')->toArray();

          if(!in_array($item->BRANCH, $brs)) {
            return abort(403);
          }
        } elseif($user->activeCompany()->company_id != $item->branch->company_id
                  || $user->activeCompany()->id != $item->BRANCH)
        {
          return abort(403);
        }

        if(auth()->user()->can('edit_manifest_shipments')){
          $disabled = false;
        }

        $headerHouse = $this->headerHouse();
        $headerDetail = $this->headerHouseDetail();
        $tariff = Tariff::all();
        $kodeDocs = KodeDok::all();

        return view('pages.manifest.shipments.create-edit', compact(['item', 'headerHouse', 'headerDetail', 'tariff', 'disabled', 'kodeDocs']));
    }

    public function update(Request $request, House $shipment)
    {
        $branch = $shipment->branch ?? activeCompany();

        $data = [
          'JH_HeaderType' => 'JOB',
          'JH_Name' => $shipment->NO_BARANG,
          'JH_Description' => $shipment->NO_MASTER_BLAWB,
          'JH_JobNum' => $shipment->NO_BARANG,
          'JH_Status' => 'WRK',
          'JH_GB' => $branch->id,
          'JH_GE' => 96,
          'JH_GC' => $branch->company_id,
          'JH_SystemCreateTimeUtc' => now()->timeZone('UTC'),
          'JH_SystemCreateUser' => \Auth::id(),
        ];

        DB::beginTransaction();

        try {
          $jobheader = ShipmentsJobHeader::updateOrCreate([
            'JH_ParentID' => $shipment->id,
            'JH_ParentTableCode' => 'TPH',
            'JH_ModelType' => 'App\Models\House'
          ], $data);

          DB::commit();

          return response()->json([
            'status' => 'OK',
            'message' => 'Create Job Billing/Cost Success.'
          ]);
        } catch (\Throwable $th) {
          DB::rollback();
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
        }

        
    }

    public function destroy($id)
    {
        //
    }

    public function download(Request $request)
    {
      $shipment = House::with('details')->findOrFail($request->shipment);
      $today = today();
      $header = $request->header;
      $company = activeCompany();

      if(!$shipment->DOID){
        DB::beginTransaction();

        try {
          $running = getRunning('DO', 'NO_SURAT', today()->format('Y-m-d'));
          $shipment->update(['DOID' => $running, 'DODATE' => $today->format('Y-m-d')]);
          DB::commit();
        } catch (\Throwable $th) {
          DB::rollback();
          throw $th;
        }

      }

      $pdf = PDF::setOption([
        'enable_php' => true,
      ]);

      if($header > 0){
        $page = 'exports.do';
      } else {
        $page = 'exports.donoheader';
      }

      $pdf->loadView($page, compact(['shipment', 'header', 'company']));

      return $pdf->stream();
    }

    public function headerHouse()
    {
      $data = collect([
        'id' => 'id',
        'NO_HOUSE_BLAWB' => 'HAWB No',
        // 'X_Ray' => 'X-Ray Date',
        'NO_FLIGHT' => 'Flight No',
        'NO_BC11' => 'BC 1.1',
        'NO_POS_BC11' => 'POS BC 1.1',
        'NO_SUBPOS_BC11' => 'Sub POS BC 1.1',
        'NM_PENERIMA' => 'Consignee',
        'JML_BRG' => 'Total Items',
        'mGrossWeight' => 'Gross Weight',
        'TPS_GateInDateTime' => 'TPSO Gate In',
        'TPS_GateOutDateTime' => 'TPSO Gate Out',
        'BC_CODE' => 'KD Response',
        'BC_STATUS' => 'Keterangan',
        'actions' => 'Actions',
      ]);

      return $data;
    }

    public function headerHouseDetail()
    {
      $data = collect([
        'id' => 'id',
        'HS_CODE' => 'HS Code',
        'UR_BRG' => 'Description',
        'IMEI1' => 'IMEI 1',
        'IMEI2' => 'IMEI 2',
        'CIF' => 'CIF',
        'BM_TRF' => 'BM Trf',
        'PPN_TRF' => 'PPN Trf',
        'PPH_TRF' => 'PPH Trf',
        'BMTP_TRF' => 'BMTP Trf (/pcs)',
        'BEstimatedBM' => 'BM',
        'BEstimatedPPN' => 'PPN',
        'BEstimatedPPH' => 'PPH',
        'BEstimatedBMTP' => 'BMTP',
        'JML_SAT_HRG' => 'Jml Sat Harga',
        'KD_SAT_HRG' => 'KD Sat Harga',
        'JML_KMS' => 'Jml Kemasan',
        'JNS_KMS' => 'Jenis Kemasan',
        'FL_BEBAS' => 'FL BEBAS',
        'NO_SKEP' => 'SKEP Num',
        'TGL_SKEP' => 'SKEP Date',
        'actions' => 'Actions',
      ]);

      return $data;
    }


    public function PrintCargoDeliveryReceipt(Request $request){
        session_write_close();
        // ob_clean();
        //Kalo semua request dibawa
        // $parameters = $request->all();
        //Kalo custom
        // switch(activeCompany()->company->id){
        //     case 1:
        //         $COMPANY = "SENATOR";
        //         break;
        //     case 2:
        //         $COMPANY = "GHITA";
        //         break;
        // }

        $shipment = House::findOrFail($request->JobShipmentPK); //cari shipment ID di model shipment
        //cek nomor DO sudah ada atau belum
        if ($shipment->DOID) {
            $NOSURAT = $shipment->DOID;
            $date = $shipment->DODATE;
        }else{
            DB::beginTransaction();

            try {
                $date = today()->format('Y-m-d');
                $type = 'DO96';
                $NOSURAT = getRunning('DO', $type, $date);
                //Save NO DO ke shipment
                $shipment->DOID = $NOSURAT;
                $shipment->DODATE = $date;
                $shipment->save();

                DB::commit();
            } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
            }
        }
        $parameters = [
                        // 'JobID' => 5963,
                        'JobID' => $request->JobShipmentPK,
                        'NO_SURAT' => $NOSURAT,
                        'TGL_SURAT' => $date,
                        // 'BLTYPE' => $request->BLTYPE,
                        'USERBY' => $request->USERBY,
                        'USERRECEIPT' => $request->USERRECEIPT,
                        'NO_TLP' => $request->NO_TLP,
                        'NO_POL' => $request->NO_POL,
                        'NM_CONSIGNEE' => $request->NM_CONSIGNEE,
                        'AL_CONSIGNEE' => $request->AL_CONSIGNEE,
                        // 'DBNAME' => GetSubDomain(),

                      ];

        // $template = '/JUSTINDO.ID/SEA_IMPORT/FORM/' . $request->FileName;
        $folderJasper = jasperFolder();
        $template = $folderJasper . '/AIR_IMPORT/FORM/' . $request->FileName;
        $fileType = 'pdf';
        $fileName = $request->FileName;

        // return view('exports.jasper', compact(['parameters', 'template', 'fileType', 'fileName']));
        return response()->view('exports.jasper', compact(['parameters', 'template', 'fileType', 'fileName']))->header('Content-Type', 'application/pdf');
    }
}
