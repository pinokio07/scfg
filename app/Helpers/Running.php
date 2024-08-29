<?php

namespace App\Helpers;
use Carbon\Carbon;
use App\Models\RunningCodeHeader;
use App\Models\RunningCodeDetail;

class Running
{
  public function getCode($module, $name, $date)
  {

    $tanggal = Carbon::parse($date);
    $day = $tanggal->format('d');
    $month = $tanggal->format('m');
    $year = $tanggal->format('Y');
    $header = RunningCodeHeader::where('module', $module)
                          ->where('name', $name)
                          ->first();
    if($header){
      $reset = $header->reset;
      $spec = $header->specific;
      $query = RunningCodeDetail::rightJoin('running_code_headers as h',
                              'running_code_details.header_id', '=', 'h.id');
      if($reset == 'daily'){
        $query->where('date', $day)
              ->where('year', $year)
              ->where('month', $month);
      } elseif($reset == 'month'){
        $query->where('year', $year)
              ->where('month', $month);
      } elseif($reset == 'year'){
        $query->where('year', $year)
              ->whereNull('month');
      }

      if($spec == true){
        $cid = activeCompany()->company->id;
        $query->where('company_id', $cid);
      }

      $running = $query->where('h.module', $module)
                      ->where('h.name', $name)
                      ->orderBy('created_at', 'desc')
                      ->select('running_code_details.*', 'h.pattern', 'h.leading_zero')
                      ->first();
      if($running){
        $seq = explode('%', $running->pattern);
        $last = $running->sequence;
        $zero = $running->leading_zero;
        $run = '';
        $roman = ['Rd', 'Rm', 'Ry', 'RY'];
        $tgls = ['d', 'm', 'M', 'y', 'Y'];

        foreach ($seq as $s) {
          if(in_array($s, $roman)){
            $run .= toRoman($tanggal->format(ltrim($s, 'R')));
          } elseif(in_array($s, $tgls)) {
            $run .= $tanggal->format($s);
          } elseif($s == 'N'){
            $run .= str_pad($last, $zero, 0, STR_PAD_LEFT);
          } else{
            $run .= $s;
          }            
        }
    
        $running->sequence = $last + 1;
        $running->save();
    
        return $run;
      }
      return "FALSE";
    }
    
    return "FALSE";    
  }

  public function setCode($module, $name, $date)
  {
    $tanggal = Carbon::parse($date);
    $day = $tanggal->format('d');
    $month = $tanggal->format('m');
    $year = $tanggal->format('Y');

    $header = RunningCodeHeader::firstOrCreate([
                'module' => $module,
                'name' => $name
              ],[
                'pattern' => $name."%d%m%Y%N",
                'reset' => 'month',
                'specific' => false,
                'leading_zero' => 5
              ]);    
    
    $reset = $header->reset;
    $spec = $header->specific;
    $query = RunningCodeDetail::rightJoin('running_code_headers as h',
                              'running_code_details.header_id', '=', 'h.id');
    if($header->wasRecentlyCreated === false){ 
      if($reset == 'daily'){
        $terakhir = $header->terakhir();
        $detail = $header->details()->firstOrCreate([
                                      'header_id' => $header->id,
                                      'date' => $day,
                                      'month' => $month,
                                      'year' => $year
                                    ],[
                                      'updated_at' => now()
                                    ]); 
        if($terakhir && $terakhir->date == '' && $terakhir->month == ''){
          $detail->sequence = $terakhir->sequence;
          $detail->save();      
        }
        $query->where('date', $day)
              ->where('year', $year)
              ->where('month', $month);
      } elseif($reset == 'month'){
        $terakhir = $header->terakhir();
        $detail = $header->details()->firstOrCreate([
                                      'header_id' => $header->id,
                                      'month' => $month,
                                      'year' => $year
                                    ],[
                                      'updated_at' => now()
                                    ]); 
        if($terakhir && $terakhir->month == ''){
          $detail->sequence = $terakhir->sequence;
          $detail->save();      
        }
        $query->where('year', $year)
              ->where('month', $month)
              ->whereNull('date');
      } elseif($reset == 'year'){
        $tahunIni = $header->tahunIni($year);       
        $seq = 1;
        if($tahunIni){
          $seq = $tahunIni->sum('sequence');      
        } 
        $detail = $header->details()->updateOrCreate([
                                    'month' => null,
                                    'year' => $year
                                  ],[
                                    'sequence' => $seq
                                  ]);
        $header->details()->where('id', '<>', $detail->id)
                          ->where('year', $year)
                          ->delete();

        $query->where('year', $year)
              ->whereNull('month');          
      } else {
        $terakhir = $header->details();
        $seq = 1;
        if($terakhir){      
          $seq = $terakhir->sum('sequence');      
        }
        $detail = $header->details()->updateOrCreate([
                                    'year' => null,
                                    'month' => null,
                                  ],[
                                    'sequence' => $seq
                                  ]);
        $header->details()->where('id', '<>', $detail->id)->delete();
      }      
    } else {
      $detail = $header->details()->create([
        'header_id' => $header->id,
        'month' => $month,
        'year' => $year,
        'sequence' => 1
      ]);
      $query->where('month', $month)
            ->where('year', $year);
    }
    
    $running = $query->where('h.module', $module)
                    ->where('h.name', $name)
                    ->orderBy('created_at', 'desc')
                    ->select('running_code_details.*', 'h.pattern', 'h.leading_zero')
                    ->first(); 
    
    $seq = explode('%', $running->pattern);
    $last = $running->sequence;
    $zero = $running->leading_zero;
    $run = '';
    $roman = ['Rd', 'Rm', 'Ry', 'RY'];
    $tgls = ['d', 'm', 'M', 'y', 'Y'];
    foreach ($seq as $s) {
      if(in_array($s, $roman)){
        $run .= toRoman($tanggal->format(ltrim($s, 'R')));
      } elseif(in_array($s, $tgls)) {
        $run .= $tanggal->format($s);
      } elseif($s == 'N'){
        $run .= str_pad($last, $zero, 0, STR_PAD_LEFT);
      } else{
        $run .= $s;
      }      
    }

    $running->sequence = $last + 1;
    $running->save();

    return $run;
  }
  
}
