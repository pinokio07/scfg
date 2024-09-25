<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Master;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\Tariff;
use App\Models\HouseTariff;
use DB, DataTables, Crypt, Auth, PDF;

class ManifestHousesController extends Controller
{        
    public function index(Request $request)
    {
      $user = Auth::user();
      if($request->ajax() || $request->has('print')){
        if($user->hasRole('super-admin'))
        {
          $query = House::withTrashed()->where('MasterID', $request->id);
        } else {
          $query = House::where('MasterID', $request->id);
        }
        
        $query->with(['print401','bclog102']);

        if($request->print > 0)
        {
          return Excel::download(new HousesExport($query), 'houses-list.xlsx');
        }

        return DataTables::of($query)
                          ->addIndexColumn()
                          ->addColumn('X_RAYDATE', function($row){
                            return $row->bclog102?->BC_DATE->format('d-M-Y H:i:s') ?? "BELUM XRAY";
                          })
                          ->addColumn('EstimatedBill', function($row){
                            return $row->HEstimatedBM + $row->HEstimatedPPH + $row->HEstimatedPPN;
                          })
                          ->editColumn('NO_HOUSE_BLAWB', function($row) use ($user){
                            $link = '';
                            $nobarang = $row->NO_HOUSE_BLAWB;
                            if($nobarang)
                            {
                              if($user->can('edit_manifest_shipments'))
                              {
                                $url = route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($row->id)]);
                              } else {
                                $url = route('manifest.shipments.show', ['shipment' => \Crypt::encrypt($row->id)]);
                              }
                              
                              $link = '<a href="'.$url.'" target="_blank">'.$nobarang.'</a>';
                            }

                            return $link;
                          })
                          ->editColumn('ChargeableWeight', function($row)
                          {
                            $cw = '<a href="#" class="editcw"
                                      data-pk="'.$row->id.'"
                                      data-url="/api/editcw"
                                      data-name="ChargeableWeight"
                                      data-title="Edit Chargable" 
                                      data-placeholder="Chargable Weight"
                                      value="'.($row->ChargeableWeight ?? 0).'">'.($row->ChargeableWeight ?? 0).'</a>';
                            
                            return $cw;
                          })
                          ->editColumn('BC_CODE', function($row){
                            $show = '';
                            if($row->BC_CODE){
                              $show .= '#'.$row->BC_CODE;
                            }

                            return $show;
                          })
                          ->addColumn('actions', function($row) use ($user){
                            $id = Crypt::encrypt($row->id);
                            $btn = '';    
                            
                            if($user->hasRole('super-admin') && $row->deleted_at)
                            {
                              $btn .= ' <button data-href="'.route('restore.data', ['model' => 'App\Models\House', 'id' => Crypt::encrypt($row->id)]).'" class="btn btn-xs elevation-2 btn-info restorehouse"><i class="fas fa-trash-restore"></i></a>';
                            } else {
                              $btn .= '<button class="btn btn-xs btn-warning elevation-2 mr-1 edit"
                                              data-toggle="tooltip"
                                              data-target="collapseHouse"
                                              title="Edit"
                                              data-id="'.$id.'">
                                        <i class="fas fa-edit"></i>
                                      </button>';

                              $btn .= '<button class="btn btn-xs btn-info elevation-2 mr-1 codes"
                                              data-toggle="tooltip"
                                              data-target="collapseHSCodes"
                                              title="HS Codes"
                                              data-id="'.$row->id.'"
                                              data-house="'.$id.'"
                                              data-code="'.$row->NO_HOUSE_BLAWB.'">
                                        <i class="fas fa-clipboard-list"></i>
                                      </button>';

                              $btn .= '<button class="btn btn-xs btn-success elevation-2 mr-1 response"
                                              data-toggle="tooltip"
                                              data-target="collapseResponse"
                                              title="Response"
                                              data-id="'.$row->id.'"
                                              data-code="'.$row->NO_HOUSE_BLAWB.'">
                                        <i class="fas fa-sync"></i>
                                      </button>';

                              $btn .= '<button class="btn btn-xs bg-fuchsia elevation-2 mr-1 calculate"
                                              data-toggle="tooltip"
                                              data-target="collapseCalculate"
                                              title="Calculate"
                                              data-id="'.$id.'"
                                              data-code="'.$row->NO_HOUSE_BLAWB.'">
                                        <i class="fas fa-calculator"></i>
                                      </button>';                                
                              
                              $btn .= '<button type="button" 
                                        class="btn btn-xs btn-success dropdown-toggle dropdown-icon" 
                                        data-toggle="dropdown">
                                          <i class="fa fa-print"></i>
                                      </button>
                                      <div class="dropdown-menu">                
                                        <a class="dropdown-item cdr" href="#" 
                                          id="btnPrintCargoDeliveryReceipt" 
                                          data-id="'.$row->id.'"
                                          data-toggle="modal" 
                                          data-target="#modal-PrintCargoDeliveryReceipt">
                                          Cargo Delivery Receipt</a>
                                        <a href="'.route('download.manifest.label', ['house' => $id]).'"
                                          class="dropdown-item" 
                                          target="_blank">Label</a>';
                                          
                              if($row->print401)
                              {
                                $printUrl = route('logs.cetak', ['id' => \Crypt::encrypt($row->print401?->LogID)]);

                                $btn .= '<a href="'.$printUrl.'"
                                            class="dropdown-item" 
                                            target="_blank">SPPBMCP</a>';
                              }

                              $btn .= '<a href="'.route('download.manifest.label', ['house' => \Crypt::encrypt($row->id)]).'?format=xml"
                                            class="dropdown-item" 
                                            target="_blank">XML</a>';

                              $btn .= '</div>';
                              
                              if($user->can('delete_manifest_shipments')){

                                $btn .= '<button class="btn btn-xs btn-danger elevation-2 hapusHouse"
                                            data-href="'. route('houses.destroy', ['house' => Crypt::encrypt($row->id)]) .'">
                                          <i class="fas fa-trash"></i>
                                        </button>';
                                        
                              }
                            }

                            return $btn;
                          })
                          ->rawColumns(['actions', 'NO_HOUSE_BLAWB', 'ChargeableWeight'])
                          ->toJson();
      }
    }
    
    public function show(House $house)
    {
        $house->load(['details', 'estimatedTariff', 'unlocoOrigin', 'unlocoTransit', 'unlocoDestination', 'unlocoBongkar']);
        
        return response()->json($house);
    }
    
    public function update(Request $request, House $house)
    {
        if(Auth::user()->cannot('edit_manifest_consolidations') 
            && Auth::user()->cannot('edit_manifest_shipments')){
          if($request->ajax()){
            return response()->json(['status' => 'Failed', 'message' => 'You are not authorized to edit this data.']);
          }
          return abort(403);
        }
        $data = $this->validatedHouse();

        if($data){
          DB::beginTransaction();

          try {

            $hasil = array_merge($data, ['NO_BARANG' => $data['NO_HOUSE_BLAWB']]);

            $house->update($hasil);            

            DB::commit();

            if(!empty($house->getChanges())){
              $info = 'Update House '.$house->mawb_parse.' <br> <ul>';

              foreach ($house->getChanges() as $key => $value) {
                if($key != 'updated_at'){
                  $info .= '<li> Update ' . $key . ' to ' . $value .'</li>';

                  if($key == 'BRUTO'){
                    $master = $house->master;
                    $newGross = $master->houses()->sum('BRUTO');
                    $master->update(['GW' => $newGross]);

                    createLog('App\Models\Master', $master->id, 'Update GW to '.$value);
                  }

                  if($key == 'SPPBNumber'){

                    $bccode = $house->BC_CODE ?? '401';

                    $house->update([                      
                      'NO_SPPB' => $house->SPPBNumber,
                      'TGL_SPPB' => $house->SPPBDate,
                      'BC_CODE' => $bccode,
                      'BC_STATUS' => 'SPPB PIB No. '.$house->SPPBNumber.' TGL : '.$house->SPPBDate.' AJU : ',
                    ]);
                  }
                }                
              }

              $info .= '</ul>';

              createLog('App\Models\House', $house->id, $info);

              DB::commit();
            }

            $house->refresh();

            // if($request->UR_BRG != ''){
            //   $hscode = HouseDetail::updateOrCreate([
            //     'HouseID' => $house->id,
            //     'HS_CODE' => '00000000'
            //   ],[
            //     'UR_BRG' => $request->UR_BRG
            //   ]);

            //   DB::commit();

            //   if($house->wasRecentlyCreated){
            //     $hsinfo = 'Add HS Code '.$request->UR_BRG;
            //   } else {
            //     $hsinfo = 'Update HS Code to '.$request->UR_BRG;
            //   }

            //   createLog('App\Models\HouseDetail', $hscode->id, $hsinfo);

            //   DB::commit();
            // }

            $cif = $house->FREIGHT + $house->FOB + $house->ASURANSI;
            $house->update([
              'CIF' => $cif
            ]);

            DB::commit();

            if($request->ajax()){
              return response()->json(['status' => 'OK', 'house' => $house->NO_HOUSE_BLAWB]);
            }
            
            return redirect('/manifest/shipments/'.Crypt::encrypt($house->id).'/edit')->with('sukses', 'Update House Success.');

          } catch (\Throwable $th) {
            DB::rollback();
            if($request->ajax()){
              return response()->json(['status' => 'Failed', 'message' => $th->getMessage()]);
            }
            throw $th;
          }
        }
    }

    public function updateajax(Request $request)
    {
      $house = House::findOrFail($request->pk);
      $column = $request->name ?? '';

      if($column != '')
      {
        DB::beginTransaction();

        try {
          $house->update([
            $column => $request->value
          ]);

          DB::commit();

          return response()->json([
            'status' => 'OK',
            'message' => 'Update '.$column.' Success.',
            'value' => $request->value
          ]);
        } catch (\Throwable $th) {
          DB::rollback();
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
          //throw $th;
        }
      }

      
    }
    
    public function destroy(Request $request, House $house)
    {
        if(Auth::user()->cannot('edit_manifest_consolidations') 
            && Auth::user()->cannot('edit_manifest_shipments')){
          if($request->ajax()){
            return response()->json(['status' => 'Failed', 'message' => 'You are not authorized to edit this data.']);
          }
          return abort(403);
        }
        DB::beginTransaction();

        try {
          $master = $house->MasterID;
          $hid = $house->id;
          $mawb = $house->mawb_parse;

          $house->delete();

          createLog('App\Models\House', $hid, 'Delete House '.$mawb);

          DB::commit();

          if($request->ajax()){
            return response()->json(['status' => "OK"]);
          }          
          
        } catch (\Throwable $th) {
          DB::rollback();

          if($request->ajax()){
            return response()->json(['status' => 'FAILED', 'message' => $th->getMessage()]);
          }
          
        }
    }

    public function calculate(Request $request, House $house)
    {
      if($request->show_estimate > 0
          || $request->show_actual > 0){
            
        if($request->show_estimate > 0){
          $tariffs = $house->estimatedTariff;
        } else if($request->show_actual > 0){
          $tariffs = $house->actualTariff;
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
          $hasVat = $tariff->vat;
          $vat = 0;
          $totalCharge = 0;
          $subTotal = 0;
          $days = $data['cal_days'];
          $date = $data['cal_out'];
          $tgl_keluar = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
          $master = $house->master;
  
          $output = '';
  
          $charges = $tariff->schema->where('is_fixed', false)
                                    ->where('is_storage', true)
                                    ->sortBy('urut');
          $others = $tariff->schema->whereNotIn('id', $charges->pluck('id')->toArray())
                                  ->sortBy('urut');
  
          foreach ($charges as $charge) {
            $column = $charge->column;
  
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
            ${'charge_'.$charge->id} = $charge->rate * ($house->$column ?? 0) * $countDays;
  
            $output .= '<tr>'
                        .'<td>
                        <input type="hidden" name="is_vat[]" value="false">
                        <input type="hidden" name="tgl_keluar[]" value="'.$tgl_keluar.'">
                        <input type="hidden" name="charge_code[]" value="'.$charge->charge_code.'">
                        <input type="hidden" name="item[]" value="'.$charge->name.'">'
                        .$charge->name.'</td>'
                        .'<td><input type="hidden" name="days[]" value="'.$countDays.'">'
                        .$countDays.'</td>'
                        .'<td><input type="hidden" name="weight[]" value="'.$house->$column.'">'
                        .number_format(($house->$column ?? 0), 2, ',','.').'</td>'
                        .'<td class="text-right"><input type="hidden" name="rate[]" value="'.$charge->rate.'">'.number_format($charge->rate, 2, ',','.').'</td>'                      
                        .'<td class="text-right"><input type="hidden" name="total[]" value="'.${'charge_'.$charge->id}.'">'.number_format((${'charge_'.$charge->id} ?? 0), 2, ',','.').'</td>';
  
            $totalCharge += ${'charge_'.$charge->id};

            if($hasVat) {
              $vat += floor(${'charge_'.$charge->id} * ($tariff->vat / 100));
            }
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

            if($hasVat) {
              $vat = floor($tariff->minimum * ($tariff->vat / 100));
            }
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
            } else {
              $sw = $house->$column;
              $column = $other->column;
              ${'other_'.$other->id} = $other->rate * $house->$column;
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
            if($hasVat) {
              $vat += floor(${'other_'.$other->id} * ($tariff->vat / 100));
            }
          }
  
          $output .= '<tr>'
                      .'<td colspan="4" class="text-right"><b>Sub Total</b></td>'
                      .'<td class="text-right"><b>'.number_format($subTotal, 2, ',', '.').'</b></td>'
                      .'</tr>';
          
          if($tariff->vat){
            // $vat = $subTotal * ($tariff->vat / 100);
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

    public function storecalculate(Request $request, House $house)
    {
        DB::beginTransaction();
        try {
          $ids = [];
          if($house->tariff->where('is_estimate', $request->is_estimate) != ''){
            $info = 'Update ';
          } else {
            $info = 'Create ';
          }

          $estimatedDate = Carbon::createFromFormat('d-m-Y', $request->cal_date);
         
          $house->update([
            'estimatedExitDate' => $estimatedDate,
            'tariff_id' => $request->cal_tariff_id
          ]);

          DB::commit();

          foreach($request->item as $key => $value){
            $tariff = HouseTariff::updateOrCreate([
              'master_id' => $house->MasterID,
              'house_id' => $house->id,
              'urut' => ($key + 1),
              'is_estimate' => $request->is_estimate
            ],[              
              'item' => $value,
              'charge_code' => $request->charge_code[$key],
              'days' => $request->days[$key],
              'tgl_keluar' => $request->tgl_keluar[$key],
              'weight' => $request->weight[$key],
              'rate' => $request->rate[$key],
              'total' => $request->total[$key],
              'is_vat' => $request->is_vat[$key]
            ]);
            DB::commit();
            $ids[] = $tariff->id;
          }

          if($request->is_estimate > 0){
            $info .= ' Estimated';
          } else {
            $info .= ' Actual';
          }

          createLog('App\Models\House', $house->id, $info.' Billing for '.$house->NO_BARANG);

          DB::commit();

          HouseTariff::where('house_id', $house->id)
                      ->whereNotIn('id', $ids)
                      ->delete();

          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'OK',
              'estimate' => $request->is_estimate,
              'id' => Crypt::encrypt($house->id),
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

    public function label(Request $request, House $house)
    {
      $house->load(['master']);

      if($request->has('format') && $request->format == 'xml')
      {
        return $this->generateXML($house);
      }

      $nobrg = $house->NO_BARANG;

      $pdf = PDF::setOption([
        'enable_php' => true,
        'chroot' => public_path()
      ]);

      // \Storage::disk('local')->put($nobrg. '.png', base64_decode(DNS1D::getBarcodePNG($house->NO_BARANG, 'C128', 1, 25, array(0, 0, 0))));

      $pdf->loadView('exports.label', compact(['house']));

      return $pdf->stream();

      // return view('exports.label', compact(['house']));
    }

    public function generateXML(House $house)
    {
      $branch = $house->branch;

      $KD_GUDANG = $branch->CB_WhCode;
      $NO_MASTER_BLAWB= str_replace(' ','',$house->NO_MASTER_BLAWB);
      $TGL_MASTER_BLAWB = bc_date($house->TGL_MASTER_BLAWB);
      $TGL_INVOICE = bc_date($house->TGL_INVOICE);
      $TGL_BC11 = bc_date($house->TGL_BC11);
      $NO_SUBPOS_BC11 = str_pad($house->NO_SUBPOS_BC11, 4, '0', STR_PAD_LEFT);
      $TGL_HOUSE_BLAWB = bc_date($house->TGL_HOUSE_BLAWB);
      $TGL_IZIN_PEMBERITAHU = bc_date($house->TGL_IZIN_PEMBERITAHU);
      $NDPBM = $house->NDPBM;
      $AL_PENGIRIM = (string)$house->AL_PENGIRIM;
      $NO_IZIN_PJT = $house->pjt?->NO_IZIN_PJT ?? $house->NO_IZIN_PEMBERITAHU;
      $TGL_IZIN_PJT = $house->pjt?->TGL_IZIN_PJT ?? $house->TGL_IZIN_PEMBERITAHU;

      if($house->FOB > 0 && $house->FREIGHT > 0) {
          $FOB_USD = $house->FOB;
          $ASURANSI = $house->ASURANSI;
          $FREIGHT = $house->FREIGHT;
          $CIFHeader = $house->CIF;
      } else {
          $FOB_USD = $house->sum_cif_dtl;
          $ASURANSI =0 ;
          $FREIGHT = 0; 
          $CIFHeader = $FOB_USD;
      }  

      $KATEGORI_BARANG_KIRIMAN = ($house->KATEGORI_BARANG_KIRIMAN > 0
                                ? $house->KATEGORI_BARANG_KIRIMAN
                                : 1);

      $AL_PENERIMA = (string)substr($house->AL_PENERIMA,0,200);                       
      $NPWP_BILLING = $house->JNS_ID_PENERIMA == 5?$house->NO_ID_PENERIMA:'000000000000000';
      $NAMA_BILLING = $house->JNS_ID_PENERIMA == 5?$house->NM_PENERIMA:$house->NM_PEMBERITAHU;

      $KD_GUDANG = ($house->BCF15_Status==="Yes"?"TPP":$KD_GUDANG);

      if($house->BCF15_Status==="Yes"){
          $NPWP_BILLING=$house->NO_ID_PEMBERITAHU;
          $NAMA_BILLING = $house->NM_PEMBERITAHU;
      }

      $DATA = "<CN_PIBK>
                <HEADER>
                    <JNS_AJU>{$house->JNS_AJU}</JNS_AJU>
                    <KD_JNS_PIBK>{$house->KD_JNS_PIBK}</KD_JNS_PIBK>
                    <KATEGORI_BARANG_KIRIMAN>{$KATEGORI_BARANG_KIRIMAN}</KATEGORI_BARANG_KIRIMAN>
                    <NO_BARANG>{$house->NO_HOUSE_BLAWB}</NO_BARANG>
                    <KD_KANTOR>{$house->KD_KANTOR}</KD_KANTOR>
                    <KD_JNS_ANGKUT>{$house->KD_JNS_ANGKUT}</KD_JNS_ANGKUT>
                    <NM_PENGANGKUT>{$house->NM_PENGANGKUT}</NM_PENGANGKUT>
                    <NO_FLIGHT>{$house->NO_FLIGHT}</NO_FLIGHT>
                    <KD_PEL_MUAT>{$house->KD_PEL_MUAT}</KD_PEL_MUAT>
                    <KD_PEL_BONGKAR>{$house->KD_PEL_BONGKAR}</KD_PEL_BONGKAR>
                    <KD_GUDANG>".$KD_GUDANG."</KD_GUDANG>
                    <NO_INVOICE>{$house->NO_INVOICE}</NO_INVOICE>
                    <TGL_INVOICE>{$TGL_INVOICE}</TGL_INVOICE>
                    <KD_NEGARA_ASAL>{$house->KD_NEGARA_ASAL}</KD_NEGARA_ASAL>
                    <JML_BRG>{$house->JML_BRG}</JML_BRG>
                    <NO_BC11>{$house->NO_BC11}</NO_BC11>
                    <TGL_BC11>{$TGL_BC11}</TGL_BC11>
                    <NO_POS_BC11>{$house->NO_POS_BC11}</NO_POS_BC11>
                    <NO_SUBPOS_BC11>{$NO_SUBPOS_BC11}</NO_SUBPOS_BC11>
                    <NO_SUBSUBPOS_BC11>{$house->NO_SUBSUBPOS_BC11}</NO_SUBSUBPOS_BC11>
                    <NO_MASTER_BLAWB>{$NO_MASTER_BLAWB}</NO_MASTER_BLAWB>
                    <TGL_MASTER_BLAWB>{$TGL_MASTER_BLAWB}</TGL_MASTER_BLAWB>
                    <NO_HOUSE_BLAWB>{$house->NO_HOUSE_BLAWB}</NO_HOUSE_BLAWB>
                    <TGL_HOUSE_BLAWB>{$TGL_HOUSE_BLAWB}</TGL_HOUSE_BLAWB>
                    <KD_NEG_PENGIRIM>{$house->KD_NEG_PENGIRIM}</KD_NEG_PENGIRIM>
                    <NM_PENGIRIM>{$house->NM_PENGIRIM}</NM_PENGIRIM>
                    <AL_PENGIRIM>{$AL_PENGIRIM}</AL_PENGIRIM>
                    <JNS_ID_PENGIRIM>5</JNS_ID_PENGIRIM>
                    <NO_ID_PENGIRIM>000000000000000</NO_ID_PENGIRIM>
                    <NO_ID_PPMSE></NO_ID_PPMSE>
                    <NM_PPMSE></NM_PPMSE>

                    <JNS_ID_PENERIMA>{$house->JNS_ID_PENERIMA}</JNS_ID_PENERIMA>
                    <NO_ID_PENERIMA>{$house->NO_ID_PENERIMA}</NO_ID_PENERIMA>
                    <NM_PENERIMA>{$house->NM_PENERIMA}</NM_PENERIMA>
                    <AL_PENERIMA>{$AL_PENERIMA}</AL_PENERIMA>
                    <TELP_PENERIMA>{$house->TELP_PENERIMA}</TELP_PENERIMA>
                    <JNS_ID_PEMBERITAHU>5</JNS_ID_PEMBERITAHU>
                    <NO_ID_PEMBERITAHU>{$house->NO_ID_PEMBERITAHU}</NO_ID_PEMBERITAHU>
                    <NM_PEMBERITAHU>{$house->NM_PEMBERITAHU}</NM_PEMBERITAHU>
                    <AL_PEMBERITAHU>{$house->AL_PEMBERITAHU}</AL_PEMBERITAHU>
                    <NO_IZIN_PEMBERITAHU>".$NO_IZIN_PJT."</NO_IZIN_PEMBERITAHU>
                    <TGL_IZIN_PEMBERITAHU>".bc_date($TGL_IZIN_PJT)."</TGL_IZIN_PEMBERITAHU>
                    <KD_VAL>USD</KD_VAL>
                    <NDPBM>{$house->NDPBM}</NDPBM>
                    <FOB>".number_format($FOB_USD,2,'.','')."</FOB>
                    <ASURANSI>".$ASURANSI."</ASURANSI>
                    <FREIGHT>".$FREIGHT."</FREIGHT>
                    <CIF>".number_format($CIFHeader,2,'.','')."</CIF>
                    <NETTO>".number_format($house->BRUTO,2,'.','')."</NETTO>
                    <BRUTO>".number_format($house->BRUTO,2,'.','')."</BRUTO>
                    <TOT_DIBAYAR>".intval($house->HEstimatedBM+$house->HEstimatedPPH+$house->HEstimatedPPN+$house->HEstimatedBMTP)."</TOT_DIBAYAR>
                      <NPWP_BILLING>{$NPWP_BILLING}</NPWP_BILLING>
                    <NAMA_BILLING>{$NAMA_BILLING}</NAMA_BILLING>
                    ";

                    $DATA .= "<DETIL>";

                    $TOTAL_BM = 0;
                    $TOTAL_BMTP = 0;
                    $TOTAL_PPN = 0;
                    $TOTAL_PPH = 0;
                    $TOTAL_PPNBM = 0;

                    foreach($house->details as $keyd => $detail)
                    {
                      $SERI = $keyd + 1;

                      $BM = $detail->BM_TRF * $detail->CIF_USD ;
                      $BMTP = $detail->BMTP_TRF*$detail->JML_SAT_HRG ;
                      $PPN = $detail->PPN_TRF * $detail->CIF_USD;
                      $PPH = $detail->PPH_TRF * $detail->CIF_USD;
                      $PPNBM = $detail->PPNBM_TRF * $detail->CIF_USD;

                      $TOTAL_BM += $BM;
                      $TOTAL_PPN += $PPN;
                      $TOTAL_PPH += $PPH;
                      $TOTAL_PPNBM += $PPNBM;
                      $TOTAL_BMTP += $BMTP;

                      $TGL_SKEP = '0000-00-00 00:00:00';

                      $tglSkep = \Carbon\Carbon::parse($detail->TGL_SKEP);
                      if($tglSkep->year > 1)
                      {
                        $TGL_SKEP = bc_date($detail->TGL_SKEP);
                      }
                      $NO_SKEP = ($detail->NO_SKEP);
                      $FL_BEBAS = ($detail->FL_BEBAS);

                      $DATA .= "
                          <BARANG>
                              <SERI_BRG>".$SERI."</SERI_BRG>
                              <HS_CODE>".$detail->HS_CODE."</HS_CODE>
                              <UR_BRG>".$detail->UR_BRG."</UR_BRG>
                              <IMEI1>".$detail->IMEI1."</IMEI1>
                              <IMEI2>".$detail->IMEI2."</IMEI2>
                              <KD_NEG_ASAL>".$house->KD_NEGARA_ASAL."</KD_NEG_ASAL>
                              <JML_KMS>".$house->JML_BRG."</JML_KMS>
                              <JNS_KMS>".$house->JNS_KMS."</JNS_KMS>
                              <CIF>".(float)$detail->CIF_USD."</CIF>
                              <KD_SAT_HRG>".$detail->KD_SAT_HRG."</KD_SAT_HRG>
                              <JML_SAT_HRG>".$detail->JML_SAT_HRG."</JML_SAT_HRG>
                              <FL_BEBAS>".$FL_BEBAS."</FL_BEBAS>
                              <NO_SKEP>".$NO_SKEP."</NO_SKEP>
                              <TGL_SKEP>".$TGL_SKEP."</TGL_SKEP>
                              <DETIL_PUNGUTAN>
                                  <KD_PUNGUTAN>1</KD_PUNGUTAN>
                                  <NILAI>".number_format(($detail->BEstimatedBM ?? 0),2,'.','')."</NILAI>
                                  <JNS_TARIF>1</JNS_TARIF>
                                  <KD_TARIF>1</KD_TARIF>
                                  <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                  <JML_SAT></JML_SAT>
                                  <TARIF>".number_format(($detail->BM_TRF ?? 0),2,'.','')."</TARIF>
                              </DETIL_PUNGUTAN>
                              <DETIL_PUNGUTAN>
                                  <KD_PUNGUTAN>2</KD_PUNGUTAN>
                                  <NILAI>".number_format(($detail->BEstimatedPPH ?? 0),2,'.','')."</NILAI>
                                  <JNS_TARIF>1</JNS_TARIF>
                                  <KD_TARIF>1</KD_TARIF>
                                  <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                  <JML_SAT></JML_SAT>
                                  <TARIF>".number_format(($detail->PPH_TRF ?? 0),2,'.','')."</TARIF>
                              </DETIL_PUNGUTAN>
                              <DETIL_PUNGUTAN>
                                  <KD_PUNGUTAN>3</KD_PUNGUTAN>
                                  <NILAI>".number_format(($detail->BEstimatedPPN ?? 0),2,'.','')."</NILAI>
                                  <JNS_TARIF>1</JNS_TARIF>
                                  <KD_TARIF>1</KD_TARIF>
                                  <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                  <JML_SAT></JML_SAT>
                                  <TARIF>".number_format(($detail->PPN_TRF ?? 0),2,'.','')."</TARIF>
                              </DETIL_PUNGUTAN>
                              <DETIL_PUNGUTAN>
                                  <KD_PUNGUTAN>4</KD_PUNGUTAN>
                                  <NILAI>".number_format(($PPNBM ?? 0),2,'.','')."</NILAI>
                                  <JNS_TARIF>1</JNS_TARIF>
                                  <KD_TARIF>1</KD_TARIF>
                                  <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                  <JML_SAT></JML_SAT>
                                  <TARIF>".number_format(($detail->PPNBM_TRF ?? 0),2,'.','')."</TARIF>
                              </DETIL_PUNGUTAN>
                              <DETIL_PUNGUTAN>
                                  <KD_PUNGUTAN>9</KD_PUNGUTAN>
                                  <NILAI>".number_format(($BMTP ?? 0),2,'.','')."</NILAI>
                                  <JNS_TARIF>1</JNS_TARIF>
                                  <KD_TARIF>2</KD_TARIF>
                                  <KD_SAT_TARIF>".$detail->KD_SAT_HRG."</KD_SAT_TARIF>
                                  <JML_SAT>".$detail->JML_SAT_HRG."</JML_SAT>
                                  <TARIF>".number_format(($detail->BMTP_TRF ?? 0),2,'.','')."</TARIF>
                              </DETIL_PUNGUTAN>
                          </BARANG>
                          ";
                    }
                    $DATA .= "</DETIL>";
                    
                  $PUNGUTAN = "<HEADER_PUNGUTAN>
                        <PUNGUTAN_TOTAL>
                            <KD_PUNGUTAN>1</KD_PUNGUTAN>
                            <NILAI>".intval($house->HEstimatedBM)."</NILAI>
                        </PUNGUTAN_TOTAL>
                        <PUNGUTAN_TOTAL>
                            <KD_PUNGUTAN>2</KD_PUNGUTAN>
                            <NILAI>".intval($house->HEstimatedPPH)."</NILAI>
                        </PUNGUTAN_TOTAL>
                        <PUNGUTAN_TOTAL>
                            <KD_PUNGUTAN>3</KD_PUNGUTAN>
                            <NILAI>".intval($house->HEstimatedPPN)."</NILAI>
                        </PUNGUTAN_TOTAL>
                        <PUNGUTAN_TOTAL>
                            <KD_PUNGUTAN>4</KD_PUNGUTAN>
                            <NILAI>".intval($TOTAL_PPNBM)."</NILAI>
                        </PUNGUTAN_TOTAL>
                          <PUNGUTAN_TOTAL>
                            <KD_PUNGUTAN>9</KD_PUNGUTAN>
                            <NILAI>".intval($house->HEstimatedBMTP)."</NILAI>
                        </PUNGUTAN_TOTAL>
                    </HEADER_PUNGUTAN>";

                      $DATA .= $PUNGUTAN."
                </HEADER>
            </CN_PIBK>
            ";

      $DATA = str_replace('&','',$DATA);

      $fileName = $house->NO_BARANG . '-'.date('Ymd'). '.xml';

      \Storage::disk('public')->put('/file/xml/'.$fileName, $DATA); 

      return \Storage::disk('public')->download('/file/xml/'.$fileName);
    }

    public function validatedHouse()
    {
      return request()->validate([
        'JNS_AJU' => 'required|numeric',
        'KD_JNS_PIBK' => 'required|numeric',
        'ShipmentNumber' => 'nullable',
        'SPPBNumber' => 'nullable',
        'SPPBDate' => 'nullable|date',
        'BCF15_Status' => 'nullable',
        'BCF15_Number' => 'nullable',
        'BCF15_Date' => 'nullable|date',
        'NO_DAFTAR_PABEAN' => 'nullable',
        'TGL_DAFTAR_PABEAN' => 'nullable|date',
        'SEAL_NO' => 'nullable',
        'SEAL_DATE' => 'nullable|date',
        'NO_HOUSE_BLAWB' => 'required',
        'TGL_HOUSE_BLAWB' => 'required|date',
        'NM_PENGIRIM' => 'required',
        'AL_PENGIRIM' => 'required',
        'NM_PENERIMA' => 'required',
        'AL_PENERIMA' => 'required',
        'NO_ID_PENERIMA' => 'nullable',
        'JNS_ID_PENERIMA' => 'nullable|numeric',
        'TELP_PENERIMA' => 'nullable',
        'NETTO' => 'nullable|numeric',
        'BRUTO' => 'nullable|numeric',
        'ChargeableWeight' => 'required|numeric',
        'FOB' => 'nullable|numeric',
        'FREIGHT' => 'nullable|numeric',
        'VOLUME' => 'nullable|numeric',
        'ASURANSI' => 'nullable|numeric',
        'JML_BRG' => 'nullable|numeric',
        'JNS_KMS' => 'nullable',
        'MARKING' => 'nullable',
        'tariff_id' => 'nullable|numeric',
        'NPWP_BILLING' => 'nullable',
        'NAMA_BILLING' => 'nullable',
        'NO_INVOICE' => 'nullable',
        'TGL_INVOICE' => 'nullable|date',
        'TOT_DIBAYAR' => 'nullable|numeric',
      ]);
    }
}
