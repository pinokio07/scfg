<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\House;
use App\Models\RefExchangeRate;
use App\Models\RefExchangeRateLegacy;
use DB, Config;

class ExRateTaxCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:exratetax';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Exchange Rate from KEMENKEU';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = today();
        $cek = RefExchangeRate::where('RE_ExRateType', 'TAX')
                              ->where('RE_ExpiryDate', '>=', $today->format('Y-m-d'))
                              ->first();
        $success = 0;
        
        if(!$cek)
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
                $newRate = RefExchangeRate::firstOrCreate([
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
                //throw $th;
                DB::rollback();
                \Log::error($th);
              }
          }
        } else {
          $newRate = $cek;
          $success++;
        }

        if($success > 0)
        {
          DB::beginTransaction();

          try {

            $hasil = House::whereIn('JNS_AJU', [1,2])
                          ->whereNull('BC_201')
                          ->update([
                            'NDPBM' => $newRate->RE_SellRate
                          ]);

            DB::commit();
            \Log::info('Success Update NDPBM for '.$hasil.' Houses');
          } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            \Log::error('Failed to update NDPBM, reason: '.$th);
          }
          
        }
        
    }
}
