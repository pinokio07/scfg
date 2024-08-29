<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RunningCodeHeader;
use App\Models\RunningCodeDetail;

class AdminRunningCodesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = RunningCodeHeader::with('details')->get();
        $items->map(function($i){
          $i->sequence = $i->details()->sum('sequence');
          $i->unsetRelation('details');
          return $i;
        });
        $items->makeHidden(['created_at', 'updated_at']);

        return view('admin.runnings.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $running_code = new RunningCodeHeader;
        $disabled = 'false';

        return view('admin.runnings.create-edit', compact(['running_code', 'disabled']));
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
          'title' => 'required',
          'module' => 'required',
          'name' => 'required'
        ]);

        if($data){
          $date = today();
          $month = $date->format('m');
          $year = $date->format('Y');

          $running_code = RunningCodeHeader::updateOrCreate([
            'module' => $request->module,
            'name' => $request->name
          ],[
            'title' => $request->title,
            'pattern' => $request->pattern ?? $request->module."%d%m%Y%N",
            'reset' => $request->reset,
            'leading_zero' => $request->leading_zero
          ]);

          if($running_code->reset == 'year'){
            $month = null;
            $day = null;
          } elseif($running_code->reset == 'month'){
            $day = null;
          } elseif($running_code->reset == 'never'){
            $month = null;
            $year = null;
            $day = null;
          }

          $details = RunningCodeDetail::updateOrCreate([
            'header_id' => $running_code->id
          ],[
            'day' => $day,
            'month' => $month,
            'year' => $year
          ]);

          return redirect('/administrator/running-codes/'.$running_code->id.'/edit')->with('sukses', 'Create Running Code Success.');
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RunningCodeHeader $running_code)
    {
        $running_code->load(['details' => function($d){
          $d->orderBy('year')
            ->orderBy('month')
            ->orderBy('date');
        }]);
        $disabled = 'disabled';

        return view('admin.runnings.create-edit', compact(['running_code', 'disabled']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RunningCodeHeader $running_code)
    {
        $running_code->load(['details' => function($d){
          $d->orderBy('year')
            ->orderBy('month')
            ->orderBy('date');
        }]);
        $disabled = 'false';

        return view('admin.runnings.create-edit', compact(['running_code', 'disabled']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RunningCodeHeader $running_code)
    {
        $data = $request->validate([
          'title' => 'required',
          'module' => 'required',
          'name' => 'required'
        ]);
        
        if($data){
          $date = today();
          $month = $date->format('m');
          $year = $date->format('Y');
          $running_code->update($request->only([
                                  'title',
                                  'module',
                                  'name',
                                  'pattern',
                                  'reset',
                                  'leading_zero'
                                ]));
          $disabled = 'false';
  
          if($running_code->reset == 'daily'){
            $terakhir = $running_code->terakhir();

            $detail = $running_code->details()->firstOrCreate([
                                          'header_id' => $running_code->id,
                                          'date' => $date->format('d'),
                                          'month' => $month,
                                          'year' => $year
                                        ],[
                                          'updated_at' => now()
                                        ]); 
            if($terakhir && $terakhir->date == ''){
              $detail->sequence = $terakhir->sequence;
              $detail->save();
            } 
          } elseif($running_code->reset == 'month'){
            $bulanIni = $running_code->bulanIni($month);
            $seq = 1;
            if($bulanIni){
              $seq = $bulanIni->sum('sequence');      
            } 

            $detail = $running_code->details()->updateOrCreate([
                                          'date' => null,
                                          'month' => $month,
                                          'year' => $year
                                        ],[                                          
                                          'sequence' => $seq
                                        ]); 

            $running_code->details()->where('id', '<>', $detail->id)
                                    ->where('year', $year)
                                    ->where('month', $month)
                                    ->delete();
          } elseif($running_code->reset == 'year'){
            $tahunIni = $running_code->tahunIni($year);       
            $seq = 1;
            if($tahunIni){
              $seq = $tahunIni->sum('sequence');      
            } 
            $detail = $running_code->details()->updateOrCreate([
                                        'date' => null,
                                        'month' => null,
                                        'year' => $year
                                      ],[
                                        'sequence' => $seq
                                      ]);
                                      
            $running_code->details()->where('id', '<>', $detail->id)
                              ->where('year', $year)
                              ->delete();
          }

          return redirect('/administrator/running-codes/'.$running_code->id.'/edit')->with('sukses', 'Update Running Code Success');
        }        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RunningCodeHeader $running_code)
    {
        $running_code->details()->delete();
        $running_code->delete();

        return redirect('/administrator/running-codes')->with('sukses', 'Delete Running Code Success');
    }

    public function editsequence(Request $request)
    {
      
      $detail = RunningCodeDetail::findOrFail($request->pk);
      $detail->sequence = $request->value;
      $detail->save();

      return "OK";
    }
}
