<?php

use Illuminate\Support\Facades\View;
use App\Helpers\Running;

function getMenu($name)
{
  $menu = \App\Models\Menu::with(['parent_items' => function ($q) {
                              $q->where('active', true)
                                ->with(['children' => function($c){
                                  $c->where('active', true)
                                    ->orderBy('order', 'asc');
                                }])
                                ->orderBy('order', 'asc');
                          }])
                          ->where('name', $name)
                          ->first();
  return (!$menu) ? '' : $menu;
}

function getPage($page)
{
  if (View::exists($page)) {
      return $page;
  }

  return 'pages.default';
}

function activeCompany()
{
  return \App\Models\GlbCompany::first();
}

function toRoman($number) {
  $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
  $returnValue = '';
  while ($number > 0) {
      foreach ($map as $roman => $int) {
          if($number >= $int) {
              $number -= $int;
              $returnValue .= $roman;
              break;
          }
      }
  }
  return $returnValue;
}

function toSQLDate($date) {
  $MonthName = explode(' ', $date)[1];
  $Date = explode(' ', $date)[0];
  $Year = explode(' ', $date)[2];
  $MonthArray = array('01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');

  $MonthNumber = array_search($MonthName, $MonthArray);

  return $Year . '-' . $MonthNumber . '-' . $Date;
}

function roundUp($number, $rounding = 0.5) {

  $roundedUp = ceil($number / $rounding) * $rounding; 

  return $roundedUp;
}

function roundDown($number, $rounding = 0.5) {

  $roundedDown = floor($number / $rounding) * $rounding;
  
  return $roundedDown;
}

function roundHalf($number, $nearest = 0.5){
  return $number + ($nearest - fmod($number, $nearest));
}

function bc_date($date)
{
  if(in_array($date,array('','0000-00-00','0000-00-00 00:00:00'))) return '';
  return date('Y/m/d',strtotime($date));
}

function subDomain()
{
  $sub = \Illuminate\Support\Arr::first(explode('.', request()->getHost()));

  return $sub;
}

function getRestrictedExt()
{
  $data = [
    'php',
    'html',
    'exe',
    'bat',
    'vba',
    'js',
    'xml',
  ];
  return $data;
}

function getNewFormat()
{
  return collect([
    'id' => 'id',
    'KD_TPS' => 'Kode TPS',
    'NM_PENGANGKUT' => 'Nama Pengangkut',
    'NO_FLIGHT' => 'No. Voy/Flight',
    'TGL_TIBA' => 'Tgl. Tiba',
    'KD_GUDANG' => 'Kd. Gudang',
    'TPS_GateInREF' => 'Ref Number',
    'NO_HOUSE_BLAWB' => 'No BL/AWB',
    'TGL_HOUSE_BLAWB' => 'Tgl BL/AWB',
    'NO_MASTER_BLAWB' => 'No Master BL/AWB',
    'TGL_MASTER_BLAWB' => 'Tgl Master BL/AWB',
    'NO_ID_PENERIMA' => 'Id Consignee',
    'NM_PENERIMA' => 'Consignee',
    'BRUTO' => 'Bruto',
    'JNS_KMS' => 'Kode Kemasan',
    'JML_BRG' => 'Jumlah Kemasan',
    'KD_DOK_INOUT' => 'Kd Dok In/Out',
    'NO_DOK_INOUT' => 'No Dok In/Out',
    'TGL_DOK_INOUT' => 'Tgl Dok In/Out',
    'WK_DOK_INOUT' => 'Waktu In/Out',
    'NO_POLISI' => 'Nomor Polisi',
    'NO_BC11' => 'No BC 11',
    'TGL_BC11' => 'Tgl BC 11',
    'NO_POS_BC11' => 'No Pos BC',
    'KD_PEL_MUAT' => 'Pel Muat',
    'KD_PEL_TRANSIT' => 'Pel Transit',
    'KD_PEL_BONGKAR' => 'Pel Bongkar',
    'NO_DAFTAR_PABEAN' => 'No Daftar Pabean',
    'TGL_DAFTAR_PABEAN' => 'Tgl Daftar Pabean',
    'SEAL_NO' => 'No Segel BC',
    'TGL_SEGEL_BC' => 'Tgl Segel BC',
  ]);
}

function createLog($model, $id, $status)
{
  \App\Models\TpsLog::create([
    'logable_type' => $model,
    'logable_id' => $id,
    'user_id' => \Auth::id() ?? 4,
    'keterangan' => $status
  ]);
}

function getRunning($module, $type, $date)
{
    //Create New Running Class
    $run = new Running;
    //Get Code
    $cek = $run->getCode($module, $type, $date);
    //Check for existing Code
    if($cek != 'FALSE'){
      //If Found, Set Variable
      $running = $cek;
    } else {
      //If Not Found, Set New Default Code
      $running = $run->setCode($module, $type, $date);
    }

    return $running;
}

function jasperFolder()
{
    $subdomain = subDomain();

    switch ($subdomain) {
        case 'dev':
            $folder = '';
            break;
        case 'uat':
            $folder = '';
            break;
        case 'fms':
            $folder = '';
            break;
        default:
            $folder = '';
            break;
    }
    return $folder;
}

function bcCodes()
{
  return [
    100,102,201,202,203,205,206,207,211,212,301,302,303,304,305,306,307,310,401,402,403,404,405,406,408,410,501,502,503,504,901,902,903,904,905,906,908,912,914,915,918
  ];
}
