<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CodeDredd\Soap\Facades\Soap;
use App\Helpers\Barkir;
use App\Helpers\Ceisa40;
use Carbon\Carbon;
use App\Exports\ManifestExport;
use App\Models\MasterPartial;
use App\Models\BillingConsolidation;
use App\Models\GlbBranch;
use App\Models\RefExchangeRate;
use App\Models\Master;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\Tariff;
use App\Models\HouseTariff;
use App\Models\KodeDok;
use App\Jobs\TarikResponJob;
use App\Jobs\TarikResponBatchJob;
use App\Jobs\KirimBatchJob;
use App\Jobs\Ceisa40Job;
use DataTables, Config, Crypt, Auth, DB, Arr;
use Excel, PDF;

class ManifestConsolidationsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = activeCompany();
        $pArr = ['201','203','307','305'];
        $rArr = ['401','408','404'];

        if($request->ajax()){
          $br = $company->id;
          if($request->branch_id)
          {
            $br = $request->branch_id;
          }
          $query = Master::where('mBRANCH', $br)
                          ->withCount([
                            'houses as pending' => function($h) use ($pArr){
                              $h->whereIn('BC_CODE', $pArr);
                            }
                          , 'houses as pendingXRAY' => function($h){
                            $h->where('BC_CODE', '501');
                          }, 'houses as released' => function($h) use ($rArr){
                            $h->whereIn('BC_CODE', $rArr);
                          }]);

          if($user->hasRole('super-admin'))
          {
            $query->withTrashed();
          }

          if($request->order[0]['column'] == 0)
          {
            $query->latest('ArrivalDate');
          }

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->editColumn('ArrivalDate', function($row){
                            if($row->ArrivalDate){
                              $time = Carbon::parse($row->ArrivalDate);
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
                          ->editColumn('MAWBNumber', function($row){
                            $mawb = $row->mawb_parse;

                            $url = url()->current().'/'.Crypt::encrypt($row->id);

                            $show = [
                              'url' => $url,
                              'raw' => $mawb
                            ];
                            // $show = '<a href="'.$url.'">'.$mawb.'</a>';

                            return $show;
                          })
                          ->addColumn('actions', function($row) use ($user){
                            $btn = '';

                            if($user->hasRole('super-admin') && $row->deleted_at)
                            {
                              $btn = ' <button data-href="'.route('restore.data', ['model' => 'App\Models\Master', 'id' => Crypt::encrypt($row->id), 'return' => route('manifest.consolidations.edit', ['consolidation' => Crypt::encrypt($row->id)])]).'" class="btn btn-xs elevation-2 btn-info restore"><i class="fas fa-trash-restore"></i></a>';
                            } else {
                              $btn = '<a data-href="'.url()->current().'/'.Crypt::encrypt($row->id).'" class="btn btn-xs elevation-2 btn-danger delete"><i class="fas fa-trash"></i></a>';
                            }

                            return $btn;
                          })
                          ->rawColumns(['MAWBNumber','UploadStatus','actions'])
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'AirlineCode' => 'Airline Code',
          'MAWBNumber' => 'MAWB Number',
          'ArrivalDate' => 'Arrival Date',
          'MasukGudang' => 'Masuk Gudang',
          'PUNumber' => 'PU Number',
          'mNoOfPackages' => 'Total Collie',
          'mGrossWeight' => 'Gross Weight',
          'HAWBCount' => 'HAWB Count',
          'pending' => 'Pending',
          'pendingXRAY' => 'PendingXRAY',
          'released' => 'Released',
          'UploadStatus' => 'Upload Status',
          'actions' => 'Actions'
        ]);

        return view('pages.manifest.consolidations.index', compact(['items', 'user']));
    }

    public function create()
    {
        $item = new Master;
        $disabled = false;
        $headerHouse = $this->headerHouse();
        $headerDetail = $this->headerHouseDetail();
        $headerPlp = $this->headerPlp();
        $tariff = Tariff::all();
        $kodeDocs = KodeDok::all();

        return view('pages.manifest.consolidations.create-edit', compact(['item', 'disabled', 'headerHouse', 'headerDetail', 'headerPlp', 'tariff', 'kodeDocs']));
    }
    
    public function store(Request $request)
    {
        $data = $this->validatedData();

        if($data){
          DB::beginTransaction();

          try {            
            $master = Master::create($data);

            DB::commit();

            $master->NPWP = $master->branch->company->GC_TaxID;
            $master->NM_PEMBERITAHU = $master->branch->company->GC_Name;
            $master->save();

            DB::commit();

            createLog('App\Models\Master', $master->id, 'Create Condolidation '.$master->mawb_parse);

            DB::commit();

            if($master->HAWBCount > 0){              
              for ($i = 1; $i <= $master->HAWBCount; $i++) { 
                $data = $this->getHouse($master, $i);

                $house = House::create($data);

                DB::commit();

                createLog('App\Models\House', $house->id, 'Create House '. $house->mawb_parse);

                DB::commit();

              }
            }

            DB::commit();

            return redirect('/manifest/consolidations/'.Crypt::encrypt($master->id).'/edit')->with('sukses', 'Create Consolidation success.');

          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }
    
    public function show(Master $consolidation, Request $request)
    {
        if($request->has('respon'))
        {
          if($request->has('ceisa') && $request->ceisa > 0)
          {
            
            $job = Ceisa40Job::dispatchAfterResponse('tarikRespon', $consolidation->id, 'mawb');

            return response()->json([
              'status' => 'OK',
              'message' => 'Proses tarik respon sedang berlangsung'
            ]);

          } elseif($request->has('take') && $request->has('skip'))
          {
            // $barkir = new Barkir;
            $take = $request->take;
            $skip = $request->skip;

            // $respon = $barkir->mintarespon($consolidation->id, $take, $skip);
            $tr = TarikResponJob::dispatchAfterResponse($consolidation, $take, $skip);
          } else {
            $tr = TarikResponBatchJob::dispatchAfterResponse($consolidation);
            // $consolidation->loadCount('houses');
            // $count = $consolidation->houses_count;
            // $take = 10;
            // $skip = 0;
            // $jobs = [];

            // while ($skip < $count) {
            //   $tr = TarikResponJob::dispatchAfterResponse($consolidation, $take, $skip);
              // $jobs[] = new TarikResponJob($consolidation, $take, $skip);
              // $skip += $take;
            // }

            // if(!empty($jobs)){
            //   \Log::notice('Start Tarik respon manual');
            //   \Bus::chain($jobs)->dispatch();
            // }
          }

          return response()->json([
            'status' => 'OK',
            'message' => 'Tarik respon success'
          ]);
        }
        $item = $consolidation->load([
          'houses.sppbmcp.billing', 'plponline', 'branch',
          'partials' => function($p) {
            $p->withCount('houses');
          }
        ]);
        $disabled = 'disabled';

        if(auth()->user()->can('edit_manifest_consolidations')){
          $disabled = false;          
        }
        
        $headerHouse = $this->headerHouse();
        $headerDetail = $this->headerHouseDetail();
        $headerPlp = $this->headerPlp();
        $tariff = Tariff::all();
        $kodeDocs = KodeDok::all();

        return view('pages.manifest.consolidations.create-edit', compact(['item', 'disabled', 'headerHouse', 'headerDetail', 'headerPlp', 'tariff', 'kodeDocs']));
    }
    
    public function edit(Master $consolidation)
    {
        $item = $consolidation->load([
          'houses.sppbmcp.billing', 'plponline', 'branch',
          'partials' => function($p) {
            $p->withCount('houses');
          }
        ]);
        $disabled = false;
        $headerHouse = $this->headerHouse();
        $headerDetail = $this->headerHouseDetail();
        $headerPlp = $this->headerPlp();
        $tariff = Tariff::all();
        $kodeDocs = KodeDok::all();

        return view('pages.manifest.consolidations.create-edit', compact(['item', 'disabled', 'headerHouse', 'headerDetail', 'headerPlp', 'tariff', 'kodeDocs']));
    }
    
    public function update(Request $request, Master $consolidation)
    {        
        if($request->has('jenis')){
          $jenis = $request->jenis;
          if($jenis == 'calculate'){
            return $this->calculateTariff($consolidation);
          } elseif($jenis == 'partial') {
            $data = $request->validate([
              'NM_ANGKUT' => 'required',
              'NO_FLIGHT' => 'required',
              'TGL_TIBA' => 'required|date',
              'JAM_TIBA' => 'required',
              'NO_BC11' => 'required',
              'TGL_BC11' => 'required|date',
              'NO_POS_BC11' => 'required',
              'TOTAL_BRUTO' => 'required'
            ]);
            
            return $this->updateOrCreatePartial($consolidation, $request);
            
          } elseif($jenis == 'alokasi') {
            $alokasi = $this->allocatePartial($consolidation);
            
            return response()->json([
              'status' => $alokasi['status'],
              'message' => $alokasi['message']
            ]);
          } elseif($jenis == 'label') {
            return $this->printLabel($request, $consolidation);
          }
        }

        $data = $this->validatedData();

        if($data){
          DB::beginTransaction();

          try {
            $consolidation->update($data);

            DB::commit();            

            $consolidation->NPWP = $consolidation->branch->company->GC_TaxID;
            $consolidation->NM_PEMBERITAHU = $consolidation->branch->company->GC_Name;
            $consolidation->save();

            $consolidation->refresh();

            DB::commit();

            for ($i = 1; $i <= $consolidation->HAWBCount; $i++) { 
              $data = $this->getHouse($consolidation, $i);
              $updated = Arr::except($data, ['MasterID', 'NO_SUBPOS_BC11']);

              $house = House::updateOrCreate([
                  'MasterID' => $consolidation->id,
                  'NO_SUBPOS_BC11' => $data['NO_SUBPOS_BC11'],
                ], $updated );

              DB::commit();

              if($house->wasRecentlyCreated){
                $info = 'Create House '.$house->mawb_parse;
              } else {
                if(!empty($house->getChanges())){
                  $sppb = 0;
                  $info = 'Update House '.$house->mawb_parse.' <br> <ul>';

                  foreach ($house->getChanges() as $hk => $hChange) {
                    if($hk != 'updated_at'){
                      $info .= '<li> Update '. $hk . ' to ' . strip_tags($hChange). '</li>';

                      if($hk == 'SPPBNumber'){
                        $sppb += 1;
                        $house->update(['NO_SPPB' => $hChange]);
                      }
                      if($hk == 'SPPBDate'){
                        $sppb += 1;
                        $house->update(['TGL_SPPB' => $hChange]);
                      }
                    }                    
                  }

                  if($sppb > 1){
                    $aju = $house->JNS_AJU;
                    switch ($aju) {
                      case 2:
                        $jnsAju = 'BC23';
                        break;
                      case 41:
                        $jnsAju = 'BC 1.6';
                        break;
                      default:
                        $jnsAju = 'PIB';
                        break;
                    }
                    $bccode = $house->BC_CODE ?? '401';

                    $house->update([
                      'BC_Code' => $bccode,
                      'BC_STATUS' => 'SPPB '.$jnsAju.' No. '.$house->SPPBNumber.' TGL : '.$house->SPPBDate.' AJU : ',
                    ]);

                    DB::commit();
                  }

                  $info .= '</ul>';
                } else {
                  $info = '';
                }
              }

              if($info != ''){
                createLog('App\Models\House', $house->id, $info);

                DB::commit();
              }

            }

            if(!empty($consolidation->getChanges())){
              $infoConsol = 'Update Consolidation <br> <ul>';

              foreach ($consolidation->getChanges() as $kc => $cChange) {
                if($kc != 'updated_at'){
                  $infoConsol .= '<li> Update '.$kc.' to '. (strip_tags($cChange) ?? 'NULL') . '</li>';
                }                
              }
              $infoConsol .= '</ul>';

              createLog('App\Models\Master', $consolidation->id, $infoConsol);

              DB::commit();
            }

            return redirect('/manifest/consolidations/'.Crypt::encrypt($consolidation->id).'/edit')->with('sukses', 'Update Consolidation success.');

          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }
    
    public function destroy(Master $consolidation)
    {
        DB::beginTransaction();

        try {
          foreach($consolidation->houses as $house)
          {
            $house->details()->delete();
          }
          // $consolidation->houses()->details()->delete();
          $consolidation->houses()->delete();
          $consolidation->plponline()->delete();

          $consolidation->delete();

          DB::commit();

          return redirect()->route('manifest.consolidations')
                           ->with('sukses', 'Delete Consolidations Success');

        } catch (\Throwable $th) {
          DB::rollback();
          throw $th;
        } 
    }

    public function calculatechargable(Request $request, Master $consolidation)
    {
      $tanggal = $request->tanggal ?? today()->format('d-m-Y');
      $tgl_keluar = Carbon::createFromFormat('d-m-Y', $tanggal)->format('Y-m-d');
      
      $gross = $consolidation->houses()
                             ->whereBetween('SCAN_OUT_DATE', [
                                $tgl_keluar. ' 00:00:00',
                                $tgl_keluar. ' 23:59:59'
                             ])
                             ->sum('BRUTO');
      $cw = $consolidation->houses()
                          ->whereBetween('SCAN_OUT_DATE', [
                            $tgl_keluar. ' 00:00:00',
                            $tgl_keluar. ' 23:59:59'
                          ])
                          ->sum('ChargeableWeight');

      return response()->json([
        'gross' => $gross,
        'cw' => $cw
      ]);      
    }

    public function calculate(Request $request, Master $consolidation)
    {
      if($request->show_estimate > 0
          || $request->show_actual > 0){
            
        if($request->show_estimate > 0){
          $tariffs = $consolidation->estimatedTariff;
        } else if($request->show_actual > 0){
          $tariffs = $consolidation->actualTariff;
        }

        $subTotal = 0;
        $output = '';

        foreach ($tariffs->where('is_vat', false) as $key => $tariff) {
          $rateShow = '';
          $weight = '';
          
          if($tariff->rate){
            $subTotal += $tariff->total;
            if($tariff->rate < 1){
              $rateShow = ($tariff->rate * 100) . ' %';
            } else {
              $rateShow = number_format($tariff->rate, 2, ',', '.');
            }
          }

          if($tariff->weight){
            $weight = number_format($tariff->weight, 2, ',', '.');
          }
          
          $output .= '<tr>'                        
                      .'<td>'.$tariff->item.'</td>'
                      .'<td>'.$tariff->days.'</td>'
                      .'<td>'.$weight.'</td>'
                      .'<td class="text-right">'.$rateShow.'</td>'
                      .'<td class="text-right">'.number_format($tariff->total, 2, ',', '.').'</td>'
                      .'</tr>';
        }

        $output .= '<tr>'
                    .'<td colspan="4" class="text-right"><b>Sub Total</b></td>'
                    .'<td class="text-right"><b>'.number_format($subTotal, 2, ',', '.').'</b></td>'
                    .'</tr>';

        $vatTariff = $tariffs->where('is_vat', true)->first();

        $output .= '<tr>'
                        .'<td colspan="4" class="text-right">'.($vatTariff?->item ?? 0).'</td>'
                        .'<td class="text-right">
                          <b>'.number_format(floor($vatTariff?->total ?? 0), 2, ',', '.').'</b></td>'
                        .'</tr>';

        $output .= '<tr>'
                    .'<td colspan="4" class="text-right"><b>TOTAL</b></td>'
                    .'<td class="text-right"><b>'.number_format(($subTotal + (floor($vatTariff->total ?? 0))), 2, ',', '.').'</b></td>'
                    .'</tr>';

      } else {
        $data = $request->validate([
          'cal_tariff' => 'required|numeric',
          'cal_days' => 'required|numeric',
          'cal_out' => 'required|date_format:d-m-Y',
        ]);
  
        if($data){
          $tariff = Tariff::with(['schema'])->findOrFail($data['cal_tariff']);

          $chargable = $request->cal_chargable;
          $gross = $request->cal_gross;
          
          $totalCharge = 0;
          $subTotal = 0;
          $days = $data['cal_days'];
          $date = $data['cal_out'];
          $tgl_keluar = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');

          $master = $consolidation->load([
            'houses' => function($h) use ($tgl_keluar){
              $h->where('ExitDate', $tgl_keluar)
                ->select('id', 'MasterID', 'BRUTO', 'NETTO', 'ChargeableWeight');
            }
          ]);

          // dd($master);
  
          $output = '';
  
          $charges = $tariff->schema->where('is_fixed', false)
                                    ->where('is_storage', true)
                                    ->sortBy('urut');
          $others = $tariff->schema->whereNotIn('id', $charges->pluck('id')->toArray())
                                  ->sortBy('urut');
  
          foreach ($charges as $charge) {
            $column = $charge->column;
            $count = 0;
            if($column == 'ChargeableWeight'){
              $count = $chargable;
            } elseif($column == 'BRUTO'){
              $count = $gross;
            }
  
            if($charge->as_one == true){
              $days -= $charge->days;
              $countDays = 1;            
            } else if($charge->days > 0){
              $countDays = ( ($days - $charge->days) > 0 ) 
                              ? $charge->days 
                              : ( ($days < 0) ? 0 : $days );
              $days -= $countDays;
              // $countDays = $charge->days;
            } else {
              $countDays = ($days < 0) ? 0 : $days;
              $days -= $countDays;
              // $countDays = $days;
            }
            ${'charge_'.$charge->id} = $charge->rate * ($count) * $countDays;
  
            $output .= '<tr>'
                        .'<td>
                        <input type="hidden" name="is_vat[]" value="false">
                        <input type="hidden" name="tgl_keluar[]" value="'.$tgl_keluar.'">
                        <input type="hidden" name="charge_code[]" value="'.$charge->charge_code.'">
                        <input type="hidden" name="item[]" value="'.$charge->name.'">'
                        .$charge->name.'</td>'
                        .'<td><input type="hidden" name="days[]" value="'.$countDays.'">'
                        .$countDays.'</td>'
                        .'<td><input type="hidden" name="weight[]" value="'.$count.'">'
                        .number_format(($count ?? 0), 2, ',','.').'</td>'
                        .'<td class="text-right"><input type="hidden" name="rate[]" value="'.$charge->rate.'">'.number_format($charge->rate, 2, ',','.').'</td>'                      
                        .'<td class="text-right"><input type="hidden" name="total[]" value="'.${'charge_'.$charge->id}.'">'.number_format((${'charge_'.$charge->id} ?? 0), 2, ',','.').'</td>';
  
            $totalCharge += ${'charge_'.$charge->id};
          }
          if($totalCharge < $tariff->minimum){
            $cc = $charges->first()->charge_code;
            $output .= '<tr>
                        <input type="hidden" name="is_vat[]" value="0">
                        <input type="hidden" name="tgl_keluar[]" value="'.$tgl_keluar.'">
                        <input type="hidden" name="charge_code[]" value="'.$cc.'">'
                      .'<td><input type="hidden" name="item[]" value="Minimum Charge">Minimum Charge</td>'
                      .'<td><input type="hidden" name="days[]" value=""></td>'
                      .'<td><input type="hidden" name="weight[]" value=""></td>'
                      .'<td><input type="hidden" name="rate[]" value=""></td>'
                      .'<td class="text-right"><input type="hidden" name="total[]" value="'.$tariff->minimum.'">'.number_format($tariff->minimum, 2, ',', '.').'</td>'
                      .'</tr>';

            $totalCharge = $tariff->minimum;
          }
          
          $subTotal += $totalCharge;
  
          foreach ($others as $other ) {
            $sw = '';
            if($other->is_fixed == true){
              ${'other_'.$other->id} = $other->rate;
            } else if($other->column == 'CDC'){
              $chPU = $others->where('name', 'Charge PU')->first()->rate ?? 0;
              $dcFee = $others->filter(function($df){
                              return str_contains($df->name, 'Admin');
                            })->first()->rate ?? 0;
              ${'other_'.$other->id} = $other->rate * ($totalCharge + $chPU + $dcFee);
            } else if($other->column == 'HAWB'){
              ${'other_'.$other->id} = $other->rate * $master->houses->count();
            } else if($other->column == 'CHARGE'){
              ${'other_'.$other->id} = $other->rate * $totalCharge;
            } else if(in_array($other->column, ['NETTO', 'BRUTO', 'ChargeableWeight'])) {
              $sw = $master->houses->sum($other->column);              
              ${'other_'.$other->id} = $other->rate * $sw;
            } else {
              $column = $other->column;
              $sw = $master->$column;              
              ${'other_'.$other->id} = $other->rate * $sw;
            }
            
            if($other->rate < 1){
              $rateShow = ($other->rate * 100) . ' %';
            } else {
              $rateShow = number_format($other->rate, 2, ',', '.');
            }
  
            $output .= '<tr>
                          <input type="hidden" name="is_vat[]" value="0">
                          <input type="hidden" name="tgl_keluar[]" value="'.$tgl_keluar.'">
                          <input type="hidden" name="charge_code[]" value="'.$other->charge_code.'">'                          
                        .'<td><input type="hidden" name="item[]" value="'.$other->name.'">'
                        .$other->name.'</td>'
                        .'<td><input type="hidden" name="days[]" value=""></td>'                      
                        .'<td><input type="hidden" name="weight[]" value="'.$sw.'">'.$sw.'</td>'
                        .'<td class="text-right"><input type="hidden" name="rate[]" value="'.$other->rate.'">'.$rateShow.'</td>'
                        .'<td class="text-right"><input type="hidden" name="total[]" value="'.${'other_'.$other->id}.'">'.number_format(${'other_'.$other->id}, 2, ',','.').'</td>'
                        .'</tr>';
  
            $subTotal += ${'other_'.$other->id};
          }
  
          $output .= '<tr>'
                      .'<td colspan="4" class="text-right"><b>Sub Total</b></td>'
                      .'<td class="text-right"><b>'.number_format($subTotal, 2, ',', '.').'</b></td>'
                      .'</tr>';

          $vat = 0;
          
          if($tariff->vat){
            $vat = $subTotal * ($tariff->vat / 100);
            $output .= '<tr>'
                        .'<td colspan="4" class="text-right">
                        <input type="hidden" name="charge_code[]" value="">'
                        .'<input type="hidden" name="is_vat[]" value="1">
                        <input type="hidden" name="tgl_keluar[]" value="'.$tgl_keluar.'">
                          <input type="hidden" name="item[]" value="VAT '.($tariff->vat + 0).' %">VAT '.($tariff->vat + 0).' %</td>'
                        .'<input type="hidden" name="days[]" value="">
                          <input type="hidden" name="weight[]" value="">
                          <input type="hidden" name="rate[]" value="">'
                        .'<td class="text-right">
                          <input type="hidden" name="total[]" value="'.floor($vat).'"><b>'.number_format(floor($vat), 2, ',', '.').'</b></td>'
                        .'</tr>';
          }
  
          $output .= '<tr>'
                      .'<td colspan="4" class="text-right"><b>TOTAL</b></td>'
                      .'<td class="text-right"><b>'.number_format(($subTotal + (floor($vat) ?? 0)), 2, ',', '.').'</b></td>'
                      .'</tr>';
          
        }        
      }

      echo $output;
      
    }

    public function storecalculate(Request $request, Master $consolidation)
    {
        DB::beginTransaction();
        try {
          $ids = [];
          $urut = 1;
          if($consolidation->tariff->where('is_estimate', $request->is_estimate) != ''){
            $info = 'Update ';
          } else {
            $info = 'Create ';
          }
         
          $consolidation->update([
            'tariff_id' => $request->cal_tariff_id
          ]);

          DB::commit();

          foreach($request->item as $key => $value){
            $tariff = HouseTariff::updateOrCreate([
              'master_id' => $consolidation->id,
              'house_id' => NULL,
              'urut' => ($key + 1),
              'is_estimate' => $request->is_estimate,
              'tgl_keluar' => $request->tgl_keluar[$key],
            ],[              
              'item' => $value,
              'charge_code' => $request->charge_code[$key],
              'days' => $request->days[$key],              
              'weight' => $request->weight[$key],
              'rate' => $request->rate[$key],
              'total' => $request->total[$key],
              'is_vat' => $request->is_vat[$key]
            ]);
            DB::commit();
            $ids[] = $tariff->id;
            $urut++;
          }

          if($request->is_estimate > 0){
            $info .= ' Estimated';
          } else {
            $info .= ' Actual';
          }

          createLog('App\Models\Master', $consolidation->id, $info.' Billing for '.$consolidation->MAWBNumber);

          DB::commit();

          HouseTariff::where('master_id', $consolidation->id)
                      ->whereNull('house_id')
                      ->where('tgl_keluar', $request->tgl_keluar[0])
                      ->where('urut', '>', $urut)
                      // ->whereNotIn('id', $ids)
                      ->delete();

          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'OK',
              'estimate' => $request->is_estimate,
              'id' => Crypt::encrypt($consolidation->id),
              'tanggal' => $request->tgl_keluar[0]
            ]);
          }

        } catch (\Throwable $th) {
          DB::rollback();

          if($request->ajax()){
            return response()->json([
              'status' => 'ERROR',
              'message' => $th->getMessage()
            ]);
          }

          throw $th;
        } 
    }

    public function calculateTariff(Master $consolidation)
    {        
      $consolidation->load(['houses' => function($h){
                      $h->where('JNS_AJU', 1)
                        ->with('details');
                    }]);

      $exrate = $this->getexrate();

      $TotalPPN = 0;
      $TotalPPH = 0;
      $TotalBM = 0;
      $TotalBMTP = 0;

      $fhs = ['87116092', '87116093', '87116094', '87116095', '87116099'];
      $subhs = ['8712', '9102', '9101', '4202', '3303', '3304', '3305', '3306', '3307'];
      $minhs = ['61','62','63','64','73'];
      $cif3 = ['4901','4902','4903','4904'];
      $cif57 = ['57'];

      DB::beginTransaction();

      try {
        foreach ($consolidation->houses as $house) {

          $house->update(['NDPBM' => $exrate]);

          DB::commit();

          $house->refresh();

          $HTotalPPN = 0;
          $HTotalPPH = 0;
          $HTotalBM = 0;
          $HTotalBMTP = 0;

          if($house->fob_cal <= 3)
          {
            foreach ($house->details as $detail) {

              $CIFHITUNG = $house->KD_VAL === "IDR" ? $detail->CIF : ($detail->CIF * $house->NDPBM);

              $hs = substr($detail->HS_CODE,0,4);

              $PPN_TRF = 0;
              $BM_TRF = 0;
              $PPH_TRF = 0;
              $BMTP_TRF = 0;
              $BMTP = NULL;

              if( !in_array($hs, $cif3) )
              {       
                $PPN_TRF = 11;
                $BMTP = 0;
                $HTotalBMTP += 0;
              }

              $NilaiImport = (($CIFHITUNG*($BM_TRF/100)))+($CIFHITUNG); 
              $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
              $PPN = floor(($NilaiImport*($PPN_TRF/100))); 
              $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));
              $BM = round((($CIFHITUNG*($BM_TRF/100))),2);

              $HTotalPPN += $PPN;
              $HTotalPPH += $PPH;
              $HTotalBM += $BM;

              $detail->update([
                'BEstimatedPPN' => $PPN,
                'BEstimatedPPH' => $PPH,
                'BEstimatedBM' => $BM,
                'BEstimatedBMTP' => $BMTP,
                'BM_TRF' => $BM_TRF,
                'BMTP_TRF' => $BMTP_TRF,
                'PPN_TRF' => $PPN_TRF,
                'PPH_TRF' => $PPH_TRF
              ]);
            }

            DB::commit();

          } elseif($house->fob_cal > 3 && $house->fob_cal <= 1500) {
            foreach($house->details as $detail) {

              $CIFHITUNG = $house->KD_VAL === "IDR" ? $detail->CIF : ($detail->CIF * $house->NDPBM);

              $hs = $detail->HS_CODE;
              $shs = substr($detail->HS_CODE,0,4);
              $mhs = substr($detail->HS_CODE,0,2);

              if(in_array($hs, $fhs)
                  || in_array($shs, $subhs)
                  || in_array($mhs, $minhs))
              {
                $detail->load(['tarifBm', 'tarifPph', 'tarifBmtp']);

                $BM_TRF = $detail->tarifBm?->BM ?? 0;
                $PPN_TRF = 11;
                $PPH_TRF = $detail->tarifPph?->PPH ?? 0;
                $BMTP_TRF = $detail->tarifBmtp?->Tarif3 ?? 0;

                if ($house->JNS_ID_PENERIMA !== "5") {
                  $PPH_TRF = $PPH_TRF*2;
                }

                $BMTP = $BMTP_TRF * $detail->JML_SAT_HRG ;
                $NilaiImport = (($CIFHITUNG*($BM_TRF/100)))+($CIFHITUNG)+$BMTP; 
                $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
                $PPN = floor(($NilaiImport*($PPN_TRF/100))); 
                $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));
                $BM = (($CIFHITUNG*($BM_TRF/100)));
                
              } elseif(in_array($shs, $cif3))
              {
                $PPN_TRF = 0;
                $BM_TRF = 0;
                $PPH_TRF = 0;

                $NilaiImport = (($CIFHITUNG*($BM_TRF/100)))+($CIFHITUNG); 
                $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
                $PPN= floor(($NilaiImport*($PPN_TRF/100))); 
                $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));
                $BM = (($CIFHITUNG*($BM_TRF/100)));
                $BMTP_TRF = 0;

                $BMTP = $BMTP_TRF * $detail->JML_SAT_HRG;

              } elseif(in_array($mhs, $cif57))
              {
                $PPN_TRF = 11;
                $BM_TRF = 7.5;
                $PPH_TRF = 0;

                $NilaiImport = (($CIFHITUNG*($BM_TRF/100)))+($CIFHITUNG); 
                $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
                $PPN= floor(($NilaiImport*($PPN_TRF/100))); 
                $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));
                $BM = (($CIFHITUNG*($BM_TRF/100)));
                $BMTP_TRF = 78027;

                $BMTP = $BMTP_TRF*$detail->JML_SAT_HRG ;
              } else {
                $PPN_TRF = 11;
                $BM_TRF = 7.5;
                $PPH_TRF = 0;
                $BMTP_TRF = 0;

                $NilaiImport = (($CIFHITUNG*($BM_TRF/100)))+($CIFHITUNG); 
                $NilaiImportDibulatkan = roundDown($NilaiImport,1000);
                $PPN= floor(($NilaiImport*($PPN_TRF/100))); 
                $PPH = ($NilaiImportDibulatkan*($PPH_TRF/100));
                $BM = (($CIFHITUNG*($BM_TRF/100)));
                $BMTP = 0;
              }

              $HTotalPPN += $PPN;
              $HTotalPPH += $PPH;
              $HTotalBM += $BM;
              $HTotalBMTP += $BMTP;

              $detail->update([
                'BEstimatedBMTP' => $BMTP,
                'BEstimatedPPN' => $PPN,
                'BEstimatedPPH' => $PPH,
                'BEstimatedBM' => $BM,
                'BMTP_TRF' => $BMTP_TRF,
                'BM_TRF' => $BM_TRF,
                'PPN_TRF' => $PPN_TRF,
                'PPH_TRF' => $PPH_TRF
              ]);

              DB::commit();
            }            
          }

          $house->update([
            'HEstimatedPPN'  => floor($HTotalPPN),
            'HEstimatedBM'   => roundUp($HTotalBM,1000),
            'HEstimatedPPH'  => floor($HTotalPPH),
            'HEstimatedBMTP'  => roundUp($HTotalBMTP,1000)
          ]);

          DB::commit();

          $TotalPPN += floor($HTotalPPN);
          $TotalPPH += floor($HTotalPPH);
          $TotalBMTP += roundUp($HTotalBMTP,1000);
          $TotalBM += roundUp($HTotalBM,1000);
        }

        $consolidation->update([
          'EstimatedPPN'  => $TotalPPN,
          'EstimatedBM'   => $TotalBM,
          'EstimatedPPH'  => $TotalPPH,
          'EstimatedBMTP'  => $TotalBMTP
        ]);

        DB::commit();

        return response()->json([
          'status' => 'OK',
          'TotalPPN' => $TotalPPN,
          'TotalPPH' => $TotalPPH,
          'TotalBM' => $TotalBM,
          'TotalBMTP' => $TotalBMTP,
        ]);
      } catch (\Throwable $th) {
        //throw $th;
        DB::rollback();

        return response()->json([
          'status' => 'ERROR',
          'message' => $th->getMessage()
        ]);
      }

    }

    public function updateOrCreatePartial(Master $consolidation, Request $request)
    {
      $consolidation->load(['pjt', 'houses']);

      $STRPAD = substr(str_pad(date('Hi').$consolidation->id,6,'0',STR_PAD_LEFT),0,6);
      $car = $consolidation->pjt?->ID_MODUL . date('Ymd') . $STRPAD;

      DB::beginTransaction();

      try {
        if($request->partial_id != '')
        {
          $partial = MasterPartial::findOrFail($request->partial_id);
          $info = 'Update';

          $partial->update([
            'NO_BC11' => $request->NO_BC11,
            'TGL_BC11' => $request->TGL_BC11,
            'NO_POS_BC11' => $request->NO_POS_BC11,
            'NM_ANGKUT' => $request->NM_ANGKUT,
            'NO_FLIGHT' => $request->NO_FLIGHT,
            'TGL_TIBA' => $request->TGL_TIBA,
            'JAM_TIBA' => $request->JAM_TIBA,
            'TOTAL_BRUTO' => $request->TOTAL_BRUTO
           ]);

        } else {
          $partial = MasterPartial::create([
                        'MasterID' => $consolidation->id,
                        'CAR' => $car,
                        'NO_BC11' => $request->NO_BC11,
                        'TGL_BC11' => $request->TGL_BC11,
                        'NO_POS_BC11' => $request->NO_POS_BC11,
                        'NM_ANGKUT' => $request->NM_ANGKUT,
                        'NO_FLIGHT' => $request->NO_FLIGHT,
                        'TGL_TIBA' => $request->TGL_TIBA,
                        'JAM_TIBA' => $request->JAM_TIBA,
                        'TOTAL_BRUTO' => $request->TOTAL_BRUTO
                      ]);
          $info = 'Create';
        }
        
        DB::commit();

        $consolidation->refresh();

        if($consolidation->partials->count() > 1)
        {
          $consolidation->update([
            'Partial' => 1
          ]);
          DB::commit();
        }

        $alokasi = $this->allocatePartial($consolidation);

        if($alokasi['status'] == 'OK')
        {
          return redirect('/manifest/consolidations/'.\Crypt::encrypt($consolidation->id).'/edit#tab-part-content')->with('sukses', $info . ' Partial Success.');
          // return redirect()->route('manifest.consolidations.edit', ['consolidation' => \Crypt::encrypt($consolidation->id)])->with('sukses', $info . ' Partial Success.');
        } else {
          return redirect('/manifest/consolidations/'.\Crypt::encrypt($consolidation->id).'/edit#tab-part-content')->with('gagal', $alokasi['message']);
          // return redirect()->route('manifest.consolidations.edit', ['consolidation' => \Crypt::encrypt($consolidation->id)])->with('gagal', $alokasi['message']);
        }

        
      } catch (\Throwable $th) {
        DB::rollback();

        return redirect()->route('manifest.consolidations.edit', ['consolidation' => \Crypt::encrypt($consolidation->id)])->with('gagal', $th->getMessage());
      }
    }

    public function allocatePartial(Master $consolidation)
    {
      $consolidation->load(['houses', 'partials']);

      $consolidation->houses()->update([
        'PartialID' => NULL
      ]);

      $consolidation->refresh();
      $barkir = new Barkir;

      DB::beginTransaction();
      try {

        $info = 'Partial Info:<br>';
        $count = 0;

        foreach($consolidation->partials->sortBy('TOTAL_BRUTO') as $partial)
        {
          $Predefined = $partial->TOTAL_BRUTO;
          $Allocated = 0;
          foreach($consolidation->houses->where('PartialID', NULL)->sortBy('id') as $house)
          {
            $Allocated += $house->BRUTO;
            if($Allocated <= $Predefined) {
              $house->update([
                'PartialID' => $partial->PartialID,
                'NO_BC11' => $partial->NO_BC11,
                'TGL_BC11' => $partial->TGL_BC11,
                'NO_POS_BC11' => $partial->NO_POS_BC11,
                'NO_FLIGHT' => $partial->NO_FLIGHT,
                'TGL_TIBA' => $partial->TGL_TIBA,
                'JAM_TIBA' => $partial->JAM_TIBA
              ]);

              DB::commit();

              $count++;              
            }
          }

          DB::commit();

          $consolidation->refresh();

          $Success = $consolidation->houses()->where('PartialID', $partial->PartialID)
                                   ->sum('BRUTO');

          $Kurang = round($Predefined-$Success,4);

          $hKurang = $consolidation->houses()->whereNull('PartialID')
                                           ->where('BRUTO', $Kurang)
                                           ->first();

          if($hKurang)
          {
            $hKurang->update([
              'PartialID' => $partial->PartialID,
              'NO_BC11' => $partial->NO_BC11,
              'TGL_BC11' => $partial->TGL_BC11,
              'NO_POS_BC11' => $partial->NO_POS_BC11,
              'NO_FLIGHT' => $partial->NO_FLIGHT,
              'TGL_TIBA' => $partial->TGL_TIBA,
              'JAM_TIBA' => $partial->JAM_TIBA
            ]);
            DB::commit();

            $count += 1;            
          }

          $partial->refresh();
          
          $barkir->updateSubPos($partial);
          DB::commit();
        }

        if($consolidation->houses->whereNull('PartialID')->count() == 1)
        {          
          $houseSisa = $consolidation->houses()->whereNull('PartialID')->first();
          
          $this->sisasatu($consolidation->id, $houseSisa);

          DB::commit();
          
          $count += 1;
        }

        $info .= 'Berhasil alokasi '.$count.' Houses';

        return [
          'status' => 'OK',
          'message' => $info
        ];
      } catch (\Throwable $th) {
        DB::rollback();

        return [
          'status' => 'ERROR',
          'message' => $th->getMessage()
        ];
      }
    }

    public function sisasatu($id, House $upHouse)
    {
        $partials = MasterPartial::where('MasterID', $id)
                                 ->with(['houses' => function($h){
                                  $h->select('id', 'PartialID', 'BRUTO')
                                    ->orderBy('id', 'desc');
                                 }])
                                 ->withSum('houses as house_bruto', 'BRUTO')
                                 ->get();
        $barkir = new Barkir;
        
        foreach($partials as $kp => $partial)
        {
          if($partial->house_bruto != $partial->TOTAL_BRUTO)
          {
            if($kp == 0)
            {
              $Kurangnya = $partial->TOTAL_BRUTO - $partial->house_bruto;
              $LastBruto = $partial->houses->first()->BRUTO;
              $NewBruto = round(($LastBruto + $Kurangnya),4);

              $house = $partial->houses->first();

              $house->update([
                'BRUTO' => $NewBruto
              ]);

            }
            if($kp == 1)
            {
              $Kurangnya = $partial->TOTAL_BRUTO - $partial->house_bruto;              
              $NewBruto = round($Kurangnya,4);

              $upHouse->update([
                'PartialID' => $partial->PartialID,
                'BRUTO' => $NewBruto,
                'NO_BC11' => $partial->NO_BC11,
                'TGL_BC11' => $partial->TGL_BC11,
                'NO_POS_BC11' => $partial->NO_POS_BC11,
                'NO_FLIGHT' => $partial->NO_FLIGHT,
                'TGL_TIBA' => $partial->TGL_TIBA,
                'JAM_TIBA' => $partial->JAM_TIBA
              ]);

              $barkir->updateSubPos($partial);
            }
          }
        }
    }

    public function kirimdata(Request $request)
    {
      $id = $request->hs ?? $request->hst;
      $bps = $request->bps ?? 0;
      if($request->has('ceisa') && $request->ceisa > 0)
      {
        $job = Ceisa40Job::dispatchAfterResponse('sendBarkir', $id, 'mawb');

        return response()->json([
          'status' => 'OK',
          'message' => 'Kirim data sedang berlangsung'
        ]);
      } elseif($id)
      {
        if(is_array($id) && count($id) > 0){
          $barkir = new Barkir;
          $send = $barkir->kirimdata($id, true, $bps);

          if($send['status'] !== 'OK') {
            return response()->json([
              'status' => $send['status'],
              'message' => $send['message']
            ]);
          }

          return response()->json([
            'status' => 'OK',
            'xml' => $send['xml']
          ]);
        } else {
          if($bps > 0)
          {
            $sep = 'preg';
            
            if(strstr($id, ",")){
              $sep = ',';
            }
            if(strstr($id, ";"))
            {
              $sep = ';';
            }
  
            if($sep == 'preg'){
              $hss = preg_split("/\r\n|\n|\r/", $id);
            } else {
              $hss = explode($sep, $id);
            }

            $imp = House::whereIn('NO_BARANG', $hss)
                        ->pluck('id')
                        ->toArray();

            $dts = implode(',', $imp);
            $id = '['.$dts.']';
          }

          $tr = KirimBatchJob::dispatchAfterResponse($id, $bps);
        }

        return response()->json([
          'status' => 'OK',
          'message' => 'Create Job Success'
        ]);
      }

      return response()->json([
        'status' => 'ERROR',
        'message' => 'House not Found'
      ]);
    }

    public function updatebc(Request $request)
    {
        $barkir = new Barkir;
        $type = 'mawb';
        $h = NULL;
        $m = $request->m;
        $id = $m;
        try {

          if($request->has('h') && $request->h != '')
          {
            $h = $request->h;
            $type = 'hawb';
            $id = $h;
          }

          if($request->has('ceisa') && $request->ceisa > 0)
          {
            $ceisa = new Ceisa40;
            
            $res = $ceisa->updateBC11($id, $type); 

            if($res['status'] != 'OK')
            {
              return response()->json([
                'status' => $res['status'],
                'message' => $res['message']
              ]);
            }

            return response()->json([
              'status' => 'OK',
              'message' => $res['message']
            ]);
          }

          $res = $barkir->updateBC11($m, $h);

          if($res != '')
          {
            if(array_key_exists('status', $res))
            {
              return response()->json([
                'status' => $res['status'],
                'message' => $res['message']
              ]);
            }
            return response()->json([
              'status' => 'OK',
              'message' => $res['respon']
            ]);
          }
        } catch (\Throwable $th) {
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
        }

       
    }

    public function printLabel(Request $request, Master $consolidation)
    {
      $mt = $request->mt;

      if($mt == 'legacy'){
        return $this->printLabelLegacy($consolidation->id);
      } elseif($mt == 'list')
      {
        return $this->printLabelList($consolidation);
      }

      if($mt == 'barcode307')
      {
        $master = $consolidation->load(['houses' => function($h) {
          $h->where('BC_CODE', 307);
        }]);
      } else {
        $master = $consolidation->load(['houses']);
      }

      $pdf = PDF::setOption([
        'enable_php' => true,
        'chroot' => public_path()
      ]);

      $pdf->loadView('exports.labelmaster', compact(['master']));

      return $pdf->stream();
    }

    public function printLabelLegacy($id)
    {
        session_write_close();
        ob_clean();

        $parameters = [
          'HouseID'  => $id
        ];
        $folder = jasperFolder();

        $template = $folder.'/AIR_IMPORT/FORM/AirWayBillOrigin';
        $fileType = 'pdf';
        $fileName = 'AirWayBillOrigin-'.$id;

        return response()->view('exports.jasper', compact(['parameters', 'template', 'fileType', 'fileName']))->header('Content-Type', 'application/pdf');
    }

    public function printLabelList(Master $consolidation)
    {
      $master = $consolidation->load(['houses']);

      $pdf = PDF::setOption([
        'enable_php' => true,
        'chroot' => public_path()
      ]);

      $pdf->loadView('exports.labellist', compact(['master']));

      return $pdf->stream();
    }

    public function download(Request $request)
    {
        $query = Master::with(['pjt']);
        $awb = $request->AWBFormat;
        $pid = $request->PartialID ?? '';

        $id = \Crypt::decrypt($request->id);
        
        $partial = MasterPartial::find($pid);

        $master = $query->findOrFail($id);
        $STRPAD = substr(str_pad(date('Hi').$master->id,6,'0',STR_PAD_LEFT),0,6);
        $car = $master->pjt?->ID_MODUL . date('Ymd') . $STRPAD;

        if($partial)
        {
          DB::beginTransaction();
          try {
            $partial->update(['CAR' => $car]);

            $master->update([
              'CAR' => $car,
              'ID_MODUL' => 'V'. $master->pjt?->ID_MODUL,
              'NO_MBLAWB' => $awb
            ]);
            
            DB::commit();
          } catch (\Throwable $th) {            
            DB::rollback();
            throw $th;
          }
        }

        session_write_close();
        ob_clean();

        $parameters = [
          'CAR'  => $master->id,
          'PartialID' => $pid
        ];

        $folder = jasperFolder();
        $template = $folder.'/AIR_IMPORT/ManifestOnline_1';
        $fileType = 'xls';
        $fileName = 'Manifest-'.$car;

        return response()->view('exports.jasper', compact(['parameters', 'template', 'fileType', 'fileName']))->header('Content-Type', 'application/vnd.ms-excel');
    }

    public function sendResponsePlp(Master $master)
    {
           
      $response = Soap::baseWsdl('https://tpsonline.beacukai.go.id/tps/service.asmx?wsdl')
                      ->withOptions([
                        'encoding' => 'UTF-8',
                        'verifypeer' => false,
                        'verifyhost' => false,
                        'soap_version' => SOAP_1_2,
                        'keep_alive' => false,
                        'connection_timeout' => 180,
                        'stream_context' => stream_context_create($opts)
                      ])
                      ->GetResponPlp_onDemands([
                        'UserName' => config('app.tps.username'),
                        'Password' => config('app.tps.password'), 
                        'KdGudang' => config('app.tps.kode_gudang'),
                        'RefNumber' => $master->pendingPlp()->REF_NUMBER, 
                      ])
                      ->call()
                      ->throw()
                      ->json();
                      
    }

    public function getHouse(Master $master, $count)
    {
      $data = [        
        'MasterID' => $master->id,
        'KD_KANTOR' => $master->KPBC,
        'NM_PENGANGKUT' => $master->NM_SARANA_ANGKUT,
        'NO_FLIGHT' => $master->FlightNo,
        'KD_PEL_MUAT' => $master->Origin,
        'KD_PEL_BONGKAR' => $master->Destination,
        'KD_GUDANG' => $master->OriginWarehouse,
        'KD_NEGARA_ASAL' => $master->unlocoOrigin->RL_RN_NKCountryCode,
        'JNS_AJU' => 4,
        'KD_DOC' => 1,
        'NO_BC11' => $master->PUNumber,
        'TGL_BC11' => $master->PUDate,
        'NO_POS_BC11' => $master->POSNumber,
        'NO_SUBPOS_BC11' => str_pad($count, 4, 0, STR_PAD_LEFT),
        'NO_SUBSUBPOS_BC11' => 0000,
        'NO_MASTER_BLAWB' => $master->MAWBNumber,
        'TGL_MASTER_BLAWB' => $master->MAWBDate,
        'KD_NEG_PENGIRIM' => $master->unlocoOrigin->RL_RN_NKCountryCode,
        'NO_ID_PEMBERITAHU' => $master->NPWP,
        'NM_PEMBERITAHU' => $master->NM_PEMBERITAHU,
        'AL_PEMBERITAHU' => $master->branch->CB_Address,
        'TGL_TIBA' => $master->ArrivalDate,
        'JAM_TIBA' => $master->ArrivalTime,
        'KD_PEL_TRANSIT' => $master->Transit,
        'KD_PEL_AKHIR' => $master->Destination,
        'BRANCH' => $master->mBRANCH,
        'PART_SHIPMENT' => $master->Partial,
      ];

      return $data;
    }

    public function getexrate()
    {
        $today = today();
        $exchange = RefExchangeRate::where('RE_ExRateType', 'TAX')
                                    ->where('RE_ExpiryDate', '>=', $today->format('Y-m-d'))
                                    ->first();

        if(!$exchange)
        {
          $url = "https://fiskal.kemenkeu.go.id/informasi-publik/kurs-pajak?date=".date('Y-m-d');
          libxml_use_internal_errors(true);
          $dom = new \DomDocument;
          $dom->loadHtmlFile($url);
          $p = $dom->getElementsByTagName('p');
          $td = $dom->getElementsByTagName('td');
  
          $TaxKMK = $p[0]->nodeValue;
          $berlaku = explode(':', $p[1]->nodeValue);
          $tgl = explode(' - ', $berlaku[1]);
          $TaxStartDate = toSQLDate(trim($tgl[0]));
          $TaxEndDate = toSQLDate(trim($tgl[1]));
          $TaxRate = str_replace('.', '', str_replace(',00', '', $td[6]->nodeValue));
          $TaxKMKDate = \Carbon\Carbon::parse($TaxStartDate)->subDay()->format('Y-m-d');
  
          if($TaxKMK)
          {
              DB::beginTransaction();
  
              try {
                $exchange = RefExchangeRate::firstOrCreate([
                  'RE_ExRateType' => 'TAX',
                  'RE_StartDate' => $TaxStartDate,
                  'RE_ExpiryDate' => $TaxEndDate
                ],[
                  'RE_SellRate' => $TaxRate,
                  'RE_RX_NKExCurrency' => 'USD',
                  'RE_Reference' => $TaxKMK,
                  'RE_ReferenceDate' => $TaxKMKDate
                ]);
  
                DB::commit();
                \Log::info('Add ExRate Success.');
                $success++;
              } catch (\Throwable $th) {
                DB::rollback();
                \Log::error($th);
              }
          }
        }        

        $exrate = $exchange->TaxRate ?? $exchange->RE_SellRate ?? 0;

        return $exrate;
    }
    
    public function headerHouse()
    {
      $data = collect([
        'id' => 'id',
        'actions' => 'Actions',
        'NO_HOUSE_BLAWB' => 'HAWB No',
        'X_RAYDATE' => 'XRAY Date',
        'NO_FLIGHT' => 'Flight No',
        'NO_BC11' => 'BC 1.1',
        'NO_POS_BC11' => 'POS BC 1.1',
        'NO_SUBPOS_BC11' => 'Sub POS BC 1.1',
        'NM_PENERIMA' => 'Consignee',
        'JML_BRG' => 'Total Items',
        'BRUTO' => 'Gross Weight',
        'ChargeableWeight' => 'Chargable',
        'SCAN_IN_DATE' => 'Scan In',
        'TPS_GateInREF' => 'Gate In Ref',
        'SCAN_OUT_DATE' => 'Scan Out',
        'TPS_GateOutREF' => 'Gate Out Ref',
        'KD_VAL' => 'KD_VAL',
        'FOB' => 'FOB',
        'FREIGHT' => 'FREIGHT',
        'ASURANSI' => 'ASURANSI',
        'CIF' => 'CIF',
        'NDPBM' => 'NDPBM',
        'HEstimatedBM' => 'Est. BM',
        'HEstimatedPPN' => 'Est. PPN',
        'HEstimatedPPH' => 'Est. PPH',
        'EstimatedBill' => 'Est. Bill',
        // 'HActualBM' => 'BM',
        // 'HActualBMTP' => 'BMTP',
        // 'HActualBMAD' => 'BMAD',
        // 'HActualPPN' => 'PPN',
        // 'HActualPPH' => 'PPH',
        // 'HActualDenda' => 'Denda',
        // 'BillingFinal' => 'Billing',
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

    public function headerPlp()
    {
      $data = collect([
        'id' => 'id',
        'created_at' => 'Waktu',
        'REF_NUMBER' => 'Reference Number',
        'Service' => 'Service',
        'reason' => 'Status',
        'Response' => 'Response'
      ]);

      return $data;
    }

    public function validatedData()
    {
      return request()->validate([
        'KPBC' => 'required',
        'mBRANCH' => 'required',
        'NPWP' => 'exclude',
        'AirlineCode' => 'required',
        'NM_SARANA_ANGKUT' => 'required',
        'FlightNo' => 'required',
        'ArrivalDate' => 'required|date',
        'ArrivalTime' => 'required',
        'Origin' => 'required',
        'Transit' => 'nullable',
        'Destination' => 'required',
        'ConsolNumber' => 'nullable',
        'MAWBNumber' => 'required|numeric',
        'MAWBDate' => 'required|date',
        'HAWBCount' => 'required|numeric',
        'mNoOfPackages' => 'nullable|numeric',
        'mGrossWeight' => 'nullable|numeric',
        'mChargeableWeight' => 'nullable|numeric',
        'Partial' => 'nullable',
        'PUNumber' => 'nullable',
        'POSNumber' => 'nullable',
        'PUDate' => 'nullable|date',
        'OriginWarehouse' => 'nullable',
        'MasukGudang' => 'nullable',
        'NO_SEGEL' => 'nullable',
      ]);
    }
}
