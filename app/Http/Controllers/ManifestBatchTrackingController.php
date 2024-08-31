<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Barkir;
use App\Models\House;
use App\Models\BcLog;
use DataTables;
use PDFMerger;

class ManifestBatchTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {       
        $items = collect([
          'id' => 'id',
          'NO_BARANG' => 'No Barang',
          'NO_MAWB' => 'No MAWB',
          'NM_PENERIMA' => 'Nama Penerima',
          'STATUS_BC' => 'Status BC',
          'cekbox' => 'cekbox',
          'RESPON' => 'Respons'
        ]);

        return view('pages.manifest.batch-tracking', compact(['items']));
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
        $np = [303, 304, 305, 306, 401, 402, 403, 404];
        if($request->ajax())
        {
          $houses = $request->houses;
          $query = House::query();

          $sep = ' ';

          if(strstr($houses, PHP_EOL))
          {
            $sep = 'preg';
          }
          if(strstr($houses, ",")){
            $sep = ',';
          }
          if(strstr($houses, ";"))
          {
            $sep = ';';
          }

          if($sep == 'preg'){
            $hss = preg_split("/\r\n|\n|\r/", $houses);
          } else {
            $hss = explode($sep, $houses);
          }

          $query->whereIn('NO_BARANG', $hss);

          if($request->has('bc_code') && $request->bc_code != '')
          {
            $bc_code = $request->bc_code;

            $query->whereHas('bclog', function($l) use ($bc_code){
                    return $l->whereIn('BC_CODE', $bc_code);
                  })
                  ->with(['bclog' => function($lw) use ($bc_code){
                    $lw->whereIn('BC_CODE', $bc_code);
                  }]);
          } else {
            $query->with(['bclog']);
          }

          $query->orderBy('NO_BARANG')->orderBy('BC_DATE', 'desc');

          return DataTables::eloquent($query)
                            ->addIndexColumn()
                            ->addColumn('NO_MAWB', function($row){
                              return $row->mawb_parse ?? "";
                            })
                            ->addColumn('NM_PENERIMA', function($row){
                              return $row->NM_PENERIMA ?? "";
                            })
                            ->addColumn('STATUS_BC', function($row){
                              return $row->BC_STATUS;
                            })
                            ->addColumn('cekbox', function($row) use ($np){
                              $cb = '';
                              foreach($row->bclog->sortByDesc('BC_DATE') as $l)
                              {
                                if(in_array($l->BC_CODE, $np)) {
                                  $cb .= '<input type="checkbox" name="bc[]" value="'.$l->LogID.'" class="check"><br>';
                                }
                              }

                              return $cb;
                            })
                            ->addColumn('RESPON', function($row) use ($np){
                              $url = '';
                              foreach($row->bclog->sortByDesc('BC_DATE') as $log){
                                $url .= $log->BC_DATE?->format('d-m-Y H:i:s') . ' => ' .$log->BC_CODE . ' | ';

                                $bctext = $log->BC_TEXT;

                                if(in_array($log->BC_CODE, $np))
                                {
                                  $url .= '<a href="'.route('logs.cetak').'?id='.\Crypt::encrypt($log->LogID).'"
                                            target="_blank">'.$bctext.'</a>';
                                } else {
                                  $url .= $bctext;
                                }

                                $url .= '</br>';
                              }                              

                              return $url;
                            })
                            ->rawColumns(['RESPON', 'cekbox'])
                            ->toJson();
        }        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
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
        $logs = BcLog::findOrFail($request->ids);
        $barkir = new Barkir;
        $nobar = [];

        $oMerger = PDFMerger::init();

        $zip = new \ZipArchive();
        $fileName = public_path('tmp/BatchMerged.zip');

        if(\File::exists($fileName) && !is_dir($fileName))
        {
          unlink($fileName);

          $fileName = public_path('tmp/BatchMerged.zip');
        }

        try {
          foreach($logs as $l)
          {
            if($l->BC_CODE == 401)
            {
              $nobar[] = $l->NO_BARANG;
            }
            $XML = simplexml_load_string(base64_decode($l->XML));

            if (isset($XML->HEADER->PDF)) {
              $PDF = base64_decode($XML->HEADER->PDF);
              $name = $l->NO_BARANG.'-'.$l->BC_CODE;
              // dd($PDF);
              // session_write_close();
              // header('Cache-Control: must-revalidate');
              // header('Pragma: public');
              // header('Content-Description: File Transfer');
              // header('Content-Disposition: inline; filename=' . $log->NO_BARANG.'-'.$log->BC_CODE . '.pdf');
              // header('Content-Transfer-Encoding: binary');
              // header('Content-Length: ' . strlen($PDF));
              // header('Content-Type: application/pdf');
              // echo $PDF;

              \Storage::disk('public')->put('tmp/'.$name.'.pdf', $PDF);

              $file = public_path('storage/tmp/'.$name.'.pdf');

              if ($zip->open($fileName, \ZipArchive::CREATE) !== TRUE) {
                return response()->json([
                  'status' => 'OK',
                  'message' => 'Could not create ZIP File'                  
                ]);
              }
              
              // $relativeName = basename($file);
              // $zip->addFile($file, $relativeName);

              if (! $zip->addFile($file, basename($file))) {
                return response()->json([
                  'status' => 'ERROR',
                  'message' => 'Could not add file to ZIP: ' . $file
                ]);
                  // echo 'Could not add file to ZIP: ' . $file;
              }

              $oMerger->addPDF($file, 'all');
            }
          }

          if(count($nobar) > 0)
          {
            $barkir->fetch401(1, $nobar);
          }

          $oMerger->merge();
          $oMerger->save(public_path('/tmp/merged_result.pdf'));
          $zip->close();

          return response()->json([
            'status' => 'OK',
            'pdf' => asset('/tmp/merged_result.pdf'),
            'zip' => asset('/tmp/BatchMerged.zip')
          ]);
        } catch (\Throwable $th) {
          //throw $th;
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ]);
        }

        
    }
}
