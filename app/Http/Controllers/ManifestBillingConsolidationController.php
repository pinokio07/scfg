<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrgHeader;
use App\Models\AccChargeCode;
use App\Models\Master;
use App\Models\BillingConsolidation;
use App\Models\BillingConsolidationDetail;
use App\Models\BillingConsolidationSppbmcp;
use App\Exports\BillingConsolidationExport;
use DataTables;
use Excel;
use Barkir;
use DB;

class ManifestBillingConsolidationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $branch = activeCompany();
        $user = \Auth::user();
        if($request->ajax())
        {
          $query = BillingConsolidation::where('BRANCH', $branch->id)
                                       ->latest('WK_REKAM');

          return DataTables::eloquent($query)
                           ->addIndexColumn()
                           ->addColumn('id', function($row){
                            return $row->BillingID;
                           })
                           ->addColumn('actions', function($row) use ($user){
                            $btn = '<a href="'.route('download.manifest.billing-consolidation', ['id' => $row->BillingID]).'"
                                       target="_blank" 
                                       class="btn btn-xs btn-success elevation-2">
                                      <i class="fas fa-file-excel"></i> Excel
                                    </a>';
                            if($user->can('create_accounting_billing_cost'))
                            {
                              $btn .= ' <button class="btn btn-xs btn-info elevation-2 jobcost"
                                                data-id="'.$row->BillingID.'">
                                          <i class="fas fa-plus"></i> Create Job Cost
                                        </button>';
                            }

                            return $btn;
                           })
                           ->rawColumns(['actions'])
                           ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'ID_PEMBERITAHU' => 'ID PEMBERITAHU',
          'NO_SPPBMCP_KONSOLIDASI' => 'NO SPPBMCP KONSOLIDASI',
          'TGL_SPPBMCP_KONSOLIDASI' => 'TGL SPPBMCP KONSOLIDASI',
          'WK_REKAM' => 'WK REKAM',
          'KODE_BILLING' => 'KODE BILLING',
          'TGL_BILLING' => 'TGL BILLING',
          'TGL_JT_TEMPO' => 'TGL JT TEMPO',
          'TOTAL_BILLING' => 'TOTAL BILLING',
          'actions' => "Actions"
        ]);

        return view('pages.manifest.billing.index', compact(['items']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $request->id;

        $sppbmcp = BillingConsolidationSppbmcp::with(['billing'])
                                              ->where('BillingID', $id)
                                              ->join('tps_houses as h', 'tps_billing_konsolidasi_sppbmcp.NO_BARANG', '=', 'h.NO_BARANG', 'left outer')
                                              // ->join('tps_billing_konsolidasi as b', 'tps_billing_konsolidasi_sppbmcp.BillingID', '=', 'b.BillingID', 'left outer')
                                              ->select(
                                                'tps_billing_konsolidasi_sppbmcp.*',
                                                // 'b.BillingID as bid',
                                                // 'b.NO_SPPBMCP_KONSOLIDASI',
                                                // 'b.KODE_BILLING',
                                                'h.id as hid',
                                                'h.MasterID',
                                                'h.NO_MASTER_BLAWB'
                                              )
                                              ->get();
        $masters = [];

        foreach($sppbmcp->groupBy('MasterID') as $house)
        {
          $OSTotal = $house->sum('TOTAL_TAGIHAN');
          $hid = $house->pluck('hid')->toArray();

          $masters[] = [
            'data' => $house->first(),
            'mid' => $house->first()->MasterID,
            'mEncrypted' => \Crypt::encrypt($house->first()->MasterID),
            'hid' => $hid,
            'total' => $OSTotal
          ];
        }

        DB::beginTransaction();
        
        try {
          $org = OrgHeader::with(['address'])
                          ->where('OH_Code', 'KEMKEUJKT001')
                          ->first();

          if(!$org)
          {
            return response()->json([
              'status' => 'ERROR',
              'message' => 'Organization not Found'
            ]);
          }
          
          foreach($masters as $m)
          {
            $master = Master::with(['branch'])
                            ->find($m['mid']);
            $amount = $m['total'];

            if($master){
              $jobheader = $master->jobheader()->firstOrCreate([
                                    'JH_ParentTableCode' => 'TPM'
                                  ],[
                                    'JH_HeaderType' => 'JOB',
                                    'JH_Name' => $master->MAWBNumber,
                                    'JH_Description' => $master->MAWBNumber,
                                    'JH_JobNum' => $master->MAWBNumber,
                                    'JH_Status' => 'WRK',
                                    'JH_GB' => $master->mBRANCH,
                                    'JH_GE' => 96,
                                    'JH_GC' => $master->branch->company_id,
                                    'JH_SystemCreateTimeUtc' => now()->timeZone('UTC'),
                                    'JH_SystemCreateUser' => \Auth::id(),
                                  ]);

              $jobcharge = $jobheader->Job_Billing()->updateOrCreate([
                                      'JR_GE' => $jobheader->JH_GE,
                                      'JR_GB' => $jobheader->JH_GB,
                                      'JR_AC' => NULL,
                                      'JR_CostReference' => $m['data']['billing']['KODE_BILLING'],
                                      'JR_IsCostPosted' => 0,
                                    ],[
                                      'JR_Desc' => NULL,
                                      'JR_OH_CostAccount' => $org->taxAddress()?->first()->id ?? $org->address()->first()->id,
                                      'JR_CostQty' => 1,
                                      'JR_CostRate' => $amount,
                                      'JR_OSCostAmt' => $amount,
                                      'JR_OSCostExRate' => 1,
                                      'JR_EstimatedCost' => $amount,
                                      'JR_LocalCostAmt' => $amount,
                                      'JR_OSCostGSTAmt' => 0,
                                      'JR_TotalCostAmt' => $amount,
                                      'JR_RX_NKCostCurrency' => 'IDR',
                                      'JR_APLinePostingStatus' => 'CST',
                                      'JR_APInvoiceDate' => $m['data']['billing']['TGL_BILLING'],
                                      'JR_PaymentDate' => $m['data']['billing']['TGL_JT_TEMPO']
                                    ]);

              $jobcharge->houses()->sync($m['hid']);

              DB::commit();
            }
          }

          return response()->json([
            'status' => 'OK',
            'master' => $masters
          ]);

        } catch (\Throwable $th) {
          DB::rollback();
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function download(Request $request)
    {
      if($request->id){
        $id = $request->id;

        $billing = BillingConsolidation::findOrFail($id);

        // if(!$billing || $billing->BillFetchStatus == false)
        // {
          $barkir = new Barkir;

          $res = $barkir->fetch401($id);
        
          if($res['status'] !== 'OK')
          {
            return $res['message'];
          }
        // }        

        return Excel::download(new BillingConsolidationExport($id), 'BillingKolektif-'.$id.'.xlsx');
      } 
      
      return redirect()->route('manifest.billing-consolidation');
    }
}
