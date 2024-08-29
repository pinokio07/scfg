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

            if($request->UR_BRG != ''){
              $hscode = HouseDetail::updateOrCreate([
                'HouseID' => $house->id,
                'HS_CODE' => '00000000'
              ],[
                'UR_BRG' => $request->UR_BRG
              ]);

              DB::commit();

              if($house->wasRecentlyCreated){
                $hsinfo = 'Add HS Code '.$request->UR_BRG;
              } else {
                $hsinfo = 'Update HS Code to '.$request->UR_BRG;
              }

              createLog('App\Models\HouseDetail', $hscode->id, $hsinfo);

              DB::commit();
            }

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
      $master = $house->master;
      if(!$master->OriginWarehouse){
        return response()->json([
          'status' => 'ERROR',
          'message' => 'Please Select Lini 1 Warehouse'
        ]);
      }
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
                        .'<td colspan="4" class="text-right">'.$vatTariff->item.'</td>'
                        .'<td class="text-right">
                          <b>'.number_format(round($vatTariff->total), 2, ',', '.').'</b></td>'
                        .'</tr>';

        $output .= '<tr>'
                    .'<td colspan="4" class="text-right"><b>TOTAL</b></td>'
                    .'<td class="text-right"><b>'.number_format(($subTotal + (round($vatTariff->total))), 2, ',', '.').'</b></td>'
                    .'</tr>';

      } else {
        $data = $request->validate([
          'cal_tariff' => 'required|numeric',
          'cal_days' => 'required|numeric',
        ]);
  
        if($data){
          $lini1 = $master->warehouseLine1;
          $liniRate = $lini1->tariff ?? 0;
          $tariff = Tariff::with(['schema'])->findOrFail($data['cal_tariff']);
          
          $totalCharge = 0;
          $subTotal = 0;
          $days = $data['cal_days'];
          $master = $house->master;
  
          $output = '';
  
          $charges = $tariff->schema->where('is_fixed', false)
                                    ->where('column', 'ChargeableWeight')
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
            } else {
              $countDays = ($days < 0) ? 0 : $days;
              $days -= $countDays;
            }
            
            $chargeRate = ($charge->rate > 0) ? $charge->rate : $liniRate;

            ${'charge_'.$charge->id} = $chargeRate * ($house->$column ?? 0) * $countDays;
  
            $output .= '<tr>'
                        .'<td>
                        <input type="hidden" name="is_vat[]" value="false">
                        <input type="hidden" name="item[]" value="'.$charge->name.'">'
                        .$charge->name.'</td>'
                        .'<td><input type="hidden" name="days[]" value="'.$countDays.'">'
                        .$countDays.'</td>'
                        .'<td><input type="hidden" name="weight[]" value="'.$house->$column.'">'
                        .number_format(($house->$column ?? 0), 2, ',','.').'</td>'
                        .'<td class="text-right"><input type="hidden" name="rate[]" value="'.$chargeRate.'">'.number_format($chargeRate, 2, ',','.').'</td>'                      
                        .'<td class="text-right"><input type="hidden" name="total[]" value="'.${'charge_'.$charge->id}.'">'.number_format((${'charge_'.$charge->id} ?? 0), 2, ',','.').'</td>';
  
            $totalCharge += ${'charge_'.$charge->id};
          }
          if($totalCharge < $tariff->minimum){
            $output .= '<tr>
                        <input type="hidden" name="is_vat[]" value="0">'
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
              $column = $other->column;
              ${'other_'.$other->id} = $other->rate * $house->$column;
            }
            
            if($other->rate < 1){
              $rateShow = ($other->rate * 100) . ' %';
            } else {
              $rateShow = number_format($other->rate, 2, ',', '.');
            }
  
            $output .= '<tr>
                          <input type="hidden" name="is_vat[]" value="0">'
                        .'<td><input type="hidden" name="item[]" value="'.$other->name.'">'
                        .$other->name.'</td>'
                        .'<td><input type="hidden" name="days[]" value=""></td>'                      
                        .'<td><input type="hidden" name="weight[]" value=""></td>'
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
                        .'<td colspan="4" class="text-right">'
                        .'<input type="hidden" name="is_vat[]" value="1">
                          <input type="hidden" name="item[]" value="VAT '.($tariff->vat + 0).' %">VAT '.($tariff->vat + 0).' %</td>'
                        .'<input type="hidden" name="days[]" value="">
                          <input type="hidden" name="weight[]" value="">
                          <input type="hidden" name="rate[]" value="">'
                        .'<td class="text-right">
                          <input type="hidden" name="total[]" value="'.round($vat).'"><b>'.number_format(round($vat), 2, ',', '.').'</b></td>'
                        .'</tr>';
          }
  
          $output .= '<tr>'
                      .'<td colspan="4" class="text-right"><b>TOTAL</b></td>'
                      .'<td class="text-right"><b>'.number_format(($subTotal + (round($vat) ?? 0)), 2, ',', '.').'</b></td>'
                      .'</tr>';
          
        }        
      }

      return response()->json([
        'status' => 'OK',
        'data' => $output
      ]);
      // echo $output;      
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
              'house_id' => $house->id,
              'item' => $value,
              'is_estimate' => $request->is_estimate
            ],[              
              'urut' => ($key + 1),
              'days' => $request->days[$key],
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

    public function label(House $house)
    {
      $house->load(['master']);
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
