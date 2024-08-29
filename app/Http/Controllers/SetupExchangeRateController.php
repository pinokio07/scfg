<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\RefExchangeRate;
use App\Models\OrgAddress;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use Excel;
use DataTables;

class SetupExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
          $data = RefExchangeRate::query();

          return DataTables::eloquent($data)
                            ->addIndexColumn()
                            ->addColumn('actions', function($row){

                              $btn = '<a href="'.url()->current().'/'.$row->id.'" class="btn btn-xs elevation-2 btn-info mr-1"><i class="fas fa-eye"></i> View</a> ';
                              $btn = $btn.'<a href="'.url()->current().'/'.$row->id.'/edit" class="btn btn-xs elevation-2 btn-warning mr-1"><i class="fas fa-edit"></i> Edit</a> ';
                              $btn = $btn.'<a href="javascript:void(0)" class="btn btn-xs elevation-2 btn-danger mr-1 delete"><i class="fas fa-trash"></i> Delete</a>';

                              return $btn;
                            })
                            ->rawColumns(['actions'])->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RE_StartDate' => 'Start Date',
          'RE_ExpiryDate' => 'Expiry Date',
          'RE_ExRateType' => 'Rate Type',
          'RE_SellRate' => 'Rate',
          'RE_RX_NKExCurrency' => 'Currency',
          'actions' => 'Actions'
        ]);

        return view('pages.setup.exchangerate.index',compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currency = \App\Models\RefCurrency::where('RX_IsActive', true)->get();
        $exchange_rate = new RefExchangeRate;
        $curr_list = \App\Models\RefCurrency::where('RX_IsActive', true)->get();
        $disabled = 'false';

        return view('pages.setup.exchangerate.create-edit', compact(['exchange_rate','disabled','curr_list']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'RE_StartDate' => 'required',
            'RE_ExpiryDate' => 'required',
            'RE_SellRate' => 'required',
            'RE_RX_NKExCurrency' => 'required'
        ]);

        if($data){
            $exchangerate = RefExchangeRate::create($request->all());

            return redirect('/setup/exchange-rate/'.$exchangerate->id.'/edit')->with('sukses','Add Exchange Rate');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RefExchangeRate $exchange_rate)
    {
        $curr_list = \App\Models\RefCurrency::where('RX_IsActive', true)->get();
        $disabled = 'disabled';

        return view('pages.setup.exchangerate.create-edit', compact(['exchange_rate','disabled','curr_list']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RefExchangeRate $exchange_rate)
    {
        $curr_list = \App\Models\RefCurrency::where('RX_IsActive', true)->get();
        $disabled = 'false';
        // dd($exchange_rate);
        return view('pages.setup.exchangerate.create-edit', compact(['exchange_rate','disabled','curr_list']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RefExchangeRate $exchange_rate)
    {
        $data = $request->validate([
            'RE_StartDate' => 'required',
            'RE_ExpiryDate' => 'required',
            'RE_SellRate' => 'required',
            'RE_RX_NKExCurrency' => 'required'
        ]);

        if($data){
            $exchange_rate->update($request->all());

            return redirect('/setup/exchange-rate/'.$exchange_rate->id.'/edit')->with('sukses','update Exchange Rate');
        }
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

    public function select2(Request $request)
    {
      $data = [];
      if($request->has('q') && $request->q != ''){
          $search = $request->q;
          $jenis = $request->jenis ?? "SELL";
          if(in_array($jenis, ['CUS', 'CAF3'])){
            $type = 'CUS';
          } else {
            $type = 'SELL';
          }
          if($request->has('EtaEtd')){
            $tgl = $request->EtaEtd;
          } else {
            $tgl = today()->format('Y-m-d');
          }
          $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                              ->where('RE_ExRateType', $type)
                              ->where('RE_StartDate', '<=', $tgl)
                              ->where('RE_ExpiryDate', '>=', $tgl)
                              ->where('RE_RX_NKExCurrency','LIKE',"%$search%")
                              ->limit(5)
                              ->get();
          if(!$data){
            $data[] = ['RE_RX_NKExCurrency' => 'IDR', 'RE_SellRate' => 1];
          }
          if($jenis == 'CAF3'){
            $data->map(function($d){
              $d->RE_SellRate = round($d->RE_SellRate + (($d->RE_SellRate / 100) * 3));

              return $d;
            });
          }
      } else {
        $data[] = ['RE_RX_NKExCurrency' => 'IDR', 'RE_SellRate' => 1];
      }

      return response()->json($data);
    }

    public function getrate(Request $request)
    {
        $data = [];
        $EtdEta = \Carbon\Carbon::parse($request->EtaEtd);

        //Get ID Org Address
        $OrgAdd = OrgAddress::findOrFail($request->OA_PK);

        //Check Local atau Overseas
        if($OrgAdd->OA_RN_NKCountryCode == "ID"){
            $dateShp = strtotime($EtdEta->format('Y-m-d'));
            $today = strtotime(today()->format('Y-m-d'));
            if($dateShp > $today){
                $dateFix = today()->format('Y-m-d');
            }else{
                $dateFix = $EtdEta->format('Y-m-d');
            }
        }else{
            $dateFix = today()->format('Y-m-d');
        }

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            if($request->OA_PK != NULL){
                //Jika Currency bukan IDR
                if($search != "IDR"){
                    if($OrgAdd->header->rateSource() == "CAF3"){
                        $data = RefExchangeRate::select("RE_RX_NKExCurrency")
                                            ->selectRaw("FLOOR((RE_SellRate*3/100)+RE_SellRate) as RE_SellRate")
                                            // ->selectRaw("(RE_SellRate+3%) as RE_SellRate")
                                            ->where('RE_ExRateType', 'CUS')
                                            // ->where('RE_StartDate', '<=', $EtdEta->format('Y-m-d'))
                                            ->where('RE_StartDate', '<=',$dateFix)
                                            ->where('RE_ExpiryDate', '>=',$dateFix)
                                            ->where('RE_RX_NKExCurrency',$search)
                                            // ->where(function($query) use($search){
                                            //   $query->where('RE_RX_NKExCurrency','LIKE',"%$search%");
                                            // })
                                            ->limit(1)
                                            ->first();

                    }elseif($OrgAdd->header->rateSource() == "CUS"){
                        $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                            ->where('RE_ExRateType', 'CUS')
                                            ->where('RE_StartDate', '<=',$dateFix)
                                            ->where('RE_ExpiryDate', '>=',$dateFix)
                                            ->where('RE_RX_NKExCurrency',$search)
                                            ->limit(1)
                                            ->first();
                    }else{
                        $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                            ->where('RE_ExRateType', 'SELL')
                                            ->where('RE_StartDate', '<=',$dateFix)
                                            ->where('RE_ExpiryDate', '>=',$dateFix)
                                            ->where('RE_RX_NKExCurrency',$search)
                                            ->limit(1)
                                            ->first();
                    }
                }else{ //Jika Currency IDR
                    $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                        ->where('RE_ExRateType', 'SELL')
                                        ->where('RE_StartDate', '<=',$dateFix)
                                        ->where('RE_ExpiryDate', '>=',$dateFix)
                                        ->where('RE_RX_NKExCurrency',$search)
                                        ->limit(1)
                                        ->first();
                }
            }else{
                $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                    ->where('RE_ExRateType', 'SELL')
                                    ->where('RE_StartDate', '<=',$dateFix)
                                    ->where('RE_ExpiryDate', '>=',$dateFix)
                                    ->where('RE_RX_NKExCurrency',$search)
                                    ->limit(1)
                                    ->first();
            }
        }


        if(isset($data)){
            return response()->json($data);
        }else{
            // $data = $OrgAdd->header->rateSource();
            $data = array('RE_SellRate'=>'0.00');
            return response()->json($data);
        }
    }

    public function getrate_Ocharge(Request $request)
    {
        $data = [];
        $EtdEta = \Carbon\Carbon::parse($request->EtaEtd);

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                ->where('RE_ExRateType', 'SELL')
                                ->where('RE_StartDate', '<=', $EtdEta->format('Y-m-d'))
                                ->where('RE_ExpiryDate', '>=', $EtdEta->format('Y-m-d'))
                                ->where(function($query) use($search){
                                  $query->where('RE_RX_NKExCurrency','LIKE',"%$search%");
                                })
                                ->limit(1)
                                ->first();
        }
        if(isset($data)){
            return response()->json($data);
        }else{
            $data = array('RE_SellRate'=>'0.00');
            return response()->json($data);
        }
    }

    public function getrate_backup28Aug22(Request $request)
    {
        $data = [];
        $EtdEta = \Carbon\Carbon::parse($request->EtaEtd);

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefExchangeRate::select("RE_RX_NKExCurrency","RE_SellRate")
                                ->where('RE_ExRateType', 'SELL')
                                ->where('RE_StartDate', '<=', $EtdEta->format('Y-m-d'))
                                ->where('RE_ExpiryDate', '>=', $EtdEta->format('Y-m-d'))
                                ->where(function($query) use($search){
                                  $query->where('RE_RX_NKExCurrency','LIKE',"%$search%");
                                })
                                ->limit(1)
                                ->first();
        }
        if(isset($data)){
            return response()->json($data);
        }else{
            $data = array('RE_SellRate'=>'0.00');
            return response()->json($data);
        }
    }

    public function syncRate(Request $request)
    {
      $url = "https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal2";

      if($request->has('date') && $request->date != ''){
        $range = explode(' - ', $request->date);
      } else {
        $today = today();
        $range[0] = $today->format('Y-m-d');
        $range[1] = $today->format('Y-m-d');
      }

      $start = $range[0];
      $end = $range[1];

      $periods = \Carbon\CarbonPeriod::create($start, $end);

      foreach ($periods as $date) {
        $data = [];
        $auah = [];

        $tgl = $date->format('Y-m-d');

        $text = $url."?tgl=".$tgl;

        try {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $text);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

          $xmlresponse = curl_exec($ch);
          curl_close($ch);
          $xmlData = simplexml_load_string($xmlresponse);

          foreach ($xmlData->xpath ("./*") as $n)
          {
              $data[] = $n;
          }
        } catch (\Throwable $th) {
          throw $th;
        }

        if(count($data[1]) > 0){

          foreach ($data[1]->NewDataSet as $key => $value) {
            foreach ($value as $v) {
              $auah[] = [
                'satuan' => (int)$v->nil_subkurslokal,
                'kurs' => (string)$v->mts_subkurslokal,
                'jual' => (int)$v->jual_subkurslokal,
                'beli' => (int)$v->beli_subkurslokal,
                'tanggal' => \Carbon\Carbon::parse($v->tgl_subkurslokal),
              ];
            }
          }



          if(count($auah) > 0){
            foreach ($auah as $key => $vl) {
              $middleKurs = (($vl['beli'] / $vl['satuan']) + ($vl['jual'] / $vl['satuan'])) / 2;

              $kursBuy = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => trim($vl['kurs']),
                'RE_ExRateType' => "BUY",
                'RE_StartDate' => $vl['tanggal']->format('Y-m-d'),
                'RE_ExpiryDate' => $vl['tanggal']->format('Y-m-d'),
              ],[
                'RE_SellRate' => round($middleKurs)
              ]);

              $kursSell = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => trim($vl['kurs']),
                'RE_ExRateType' => "SELL",
                'RE_StartDate' => $vl['tanggal']->format('Y-m-d'),
                'RE_ExpiryDate' => $vl['tanggal']->format('Y-m-d'),
              ],[
                'RE_SellRate' => round($middleKurs)
              ]);

              $kursCus = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => trim($vl['kurs']),
                'RE_ExRateType' => "CUS",
                'RE_StartDate' => $vl['tanggal']->format('Y-m-d'),
                'RE_ExpiryDate' => $vl['tanggal']->format('Y-m-d'),
              ],[
                'RE_SellRate' => round(($vl['jual'] / $vl['satuan']))
              ]);
            }
          }

        } else {
          if($date->format('w') == "6"){
            $newDate = $date->copy()->subDays(1);
          } elseif($date->format('w') == "7"){
            $newDate = $date->copy()->subDays(2);
          } else {
            $newDate = $date->copy()->subDays(1);
          }

          $exchangeRate = \App\Models\RefExchangeRate::where(
                                                'RE_StartDate',
                                                $newDate->format('Y-m-d')
                                              )
                                              ->where(
                                                'RE_ExpiryDate',
                                                $newDate->format('Y-m-d')
                                              )
                                              ->get();

          foreach ($exchangeRate as $key => $rate) {
            if($rate->RE_ExRateType == 'BUY'){
              $newRateBuy = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => $rate->RE_RX_NKExCurrency,
                'RE_ExRateType' => "BUY",
                'RE_StartDate' => $date->format('Y-m-d'),
                'RE_ExpiryDate' => $date->format('Y-m-d'),
              ],[
                'RE_SellRate' => round($rate->RE_SellRate)
              ]);
            }

            if($rate->RE_ExRateType == 'SELL'){
              $newRateSell = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => $rate->RE_RX_NKExCurrency,
                'RE_ExRateType' => "SELL",
                'RE_StartDate' => $date->format('Y-m-d'),
                'RE_ExpiryDate' => $date->format('Y-m-d'),
              ],[
                'RE_SellRate' => round($rate->RE_SellRate)
              ]);
            }

            if($rate->RE_ExRateType == 'CUS'){
              $newRateSell = \App\Models\RefExchangeRate::updateOrCreate([
                'RE_RX_NKExCurrency' => $rate->RE_RX_NKExCurrency,
                'RE_ExRateType' => "CUS",
                'RE_StartDate' => $date->format('Y-m-d'),
                'RE_ExpiryDate' => $date->format('Y-m-d'),
              ],[
                'RE_SellRate' => round($rate->RE_SellRate)
              ]);
            }
          }
        }
      }

      if($request->has('date') && $request->date != ''){
        return redirect('/setup/exchange-rate')->with('success', 'Sync Rate Success');
      }

      return "Sync Rate Success.";

    }

    public function download()
    {
      $model = '\App\Models\RefExchangeRate';
      return Excel::download(new SetupExport($model), 'exchange-rate.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefExchangeRate';
        Excel::import(new SetupImport($model), $request->upload);

        return redirect('/setup/exchange-rate')->with('sukses', 'Upload Success.');
    }
}
