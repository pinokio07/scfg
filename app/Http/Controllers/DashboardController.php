<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\DashboardExport;
use App\Models\RefExchangeRate;
use App\Models\Master;
use App\Models\House;
use App\Models\User;
use App\Models\PlpOnline;
use App\Models\PassLog;
use Carbon\Carbon;
use DataTables;
use Excel;
use Auth;
use Crypt;
use Str;
use PDF;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = $user->branches;
        $today = today();
        $abandonDate = $today->copy()->subDays(30)->format('Y-m-d');
        $diff = 0;
        $exRate = RefExchangeRate::where('RE_ExRateType', 'TAX')
                                  ->where('RE_ExpiryDate', '>=', $today->format('Y-m-d'))
                                  ->first();

        if($user->passLog->isEmpty()
            && $user->cannot('bypass-password')){
          return redirect('/profile');
        }
        
        if($request->has('idm') && $request->idm !== '')
        {
          if($request->idm !== 'all')
          {
            $idm = base64_decode($request->idm);
            $brid = [$idm];
          } else {
            $brid = $branches->pluck('id')->toArray();
          }
        } else {
          $branch = $user->activeCompany();
          $brid = [$branch->id];
        }

        if($request->ajax() || $request->has('tipe') || $request->has('count')) {

          if($request->has('count')){
            if($request->has('plp')) {
              return $this->calculatePlp($request, $brid);
            }
            if($request->has('current')) {
              return $this->calculateCurrent($request, $brid, $abandonDate);
            }
            if($request->has('abandon')) {
              return $this->calculateAbandon($request, $brid, $abandonDate);
            }
            if($request->has('stat')) {
              return $this->calculateState($request, $brid, $abandonDate);
            }
            if($request->has('oth')) {
              return $this->calculateOther($request, $brid);
            }
            if($request->has('wc')) {
              return $this->calculateCompleted($request, $brid);
            }
            // return $this->calculateShipment($request, $brid, $abandonDate);
          }

          return $this->dashboardShipment($request);
        }
        
        $items = collect([
          'id' => 'id',
          'NO_MASTER_BLAWB' => 'Master BLAWB',
          'NO_BARANG' => 'House AWB',
          'BC_CODE' => 'Status Code',
          'BC_DATE' => 'Status Date',
          'NM_PENGIRIM' => 'Nama Pengirim',
          'NM_PENERIMA' => 'Nama Penerima',
          'JML_BRG' => 'Package',          
          'BRUTO' => 'Gross',
          'NETTO' => 'Netto',
          'ChargeableWeight' => 'CW',
          'SPPBNumber' => 'No SPPB',
          'PLP' => 'No PLP',
          'TGL_TIBA' => 'Tanggal Tiba',
          'SCAN_IN_DATE' => 'Masuk Gudang',
          'ExitDate' => 'Exit Date',
          'ExitTime' => 'Exit Time',
          'TPS_GateInREF' => 'Gate In Ref',
          'TPS_GateOutREF' => 'Gate Out Ref',
          'actions' => 'Actions'
        ]);

        $skips = [
          'BC_DATE','NM_PENGIRIM','NM_PENERIMA','SPPBNumber','TPS_GateInREF','TPS_GateOutREF','ExitTime'
        ];

        return view('pages.manifest.dashboard', compact(['items', 'diff', 'branches', 'skips', 'exRate']));
      
    }

    public function dashboardShipment(Request $request)
    {
        $today = today();
        $abandonDate = $today->subDays(30)->format('Y-m-d');
        $np = [303, 304, 305, 306, 401, 402, 403, 404];
        // if($request->ajax() || $request->tipe){
          $uid = \Crypt::decrypt($request->user);
          $user = User::findOrFail($uid);
          $jenis = $request->jenis;
          $cl = $request->cl;
          $column = 'BRANCH';
          $lt = 'TGL_TIBA';
          $srt = 'NO_MASTER_BLAWB';
          if($request->has('idm') && $request->idm !== '')
          {
            if($request->idm !== 'all')
            {
              $idm = base64_decode($request->idm);
              $br = [$idm];
            } else {
              $br = $user->branches()->pluck('branch_id')->toArray();
            }
          } else {
            $branch = $user->activeCompany();
            $br = [$branch->id];
          }
          $query = House::query();

          switch ($jenis) {
            case 'pending-plp':
              $column = 'mBRANCH';
              $lt = 'ArrivalDate';
              $srt = 'MAWBNumber';
              $query = Master::whereHas('plponline', function($q) use($br){
                                return $q->where('pengajuan', true)
                                        ->where('STATUS', 'Pending')
                                        ->whereIn('CABANG', $br);
                              })
                              ->where('ArrivalDate', '>', '2024-01-01')
                              ->select('*', 'MAWBNumber as NO_MASTER_BLAWB', 'mNoOfPackages as JML_BRG', 'mGrossWeight as BRUTO', 'mChargeableWeight as ChargeableWeight', 'MasukGudang as SCAN_IN_DATE', 'ArrivalDate as TGL_TIBA')
                              ->withCount(['houses as NO_BARANG'])
                              ->withSum('houses as NETTO', 'NETTO');
              break;
            case 'pending-in-wo-plp':
              $column = 'mBRANCH';
              $lt = 'ArrivalDate';
              $srt = 'MAWBNumber';
              $query = Master::whereNull('PLPNumber')
                              ->whereDoesntHave('plponline')
                              ->where('ArrivalDate', '>', '2024-01-01')
                              ->select('*', 'MAWBNumber as NO_MASTER_BLAWB', 'mNoOfPackages as JML_BRG', 'mGrossWeight as BRUTO', 'mChargeableWeight as ChargeableWeight', 'MasukGudang as SCAN_IN_DATE', 'ArrivalDate as TGL_TIBA')
                              ->withCount(['houses as NO_BARANG'])
                              ->withSum('houses as NETTO', 'NETTO');
              break;
            case 'pending-in-plp':
              $column = 'mBRANCH';
              $lt = 'ArrivalDate';
              $srt = 'MAWBNumber';
              $query = Master::whereNotNull('PLPNumber')
                              // ->whereHas('plponline', function($p){
                              //   return $p->where('pengajuan', true)
                              //            ->where('FL_SETUJU', 'Y');
                              // })
                              ->whereDoesntHave('houses', function($h){
                                return $h->whereNotNull('SCAN_IN_DATE');
                              })
                              ->where('ArrivalDate', '>', '2024-01-01')
                              ->select('*', 'MAWBNumber as NO_MASTER_BLAWB')
                              ->select('*', 'MAWBNumber as NO_MASTER_BLAWB', 'mNoOfPackages as JML_BRG', 'mGrossWeight as BRUTO', 'mChargeableWeight as ChargeableWeight', 'MasukGudang as SCAN_IN_DATE', 'ArrivalDate as TGL_TIBA')
                              ->withCount(['houses as NO_BARANG'])
                              ->withSum('houses as NETTO', 'NETTO');
              break;
            case 'pending-sppb':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->where(function($q){
                      $q->whereNull('SPPBNumber')
                        ->orWhereNotIn('BC_CODE', [401,403,404,408]);
                    })
                    // ->whereNull('SPPBNumber')
                    ->with(['master.latestPlp', 'bclog' => function($bc) use ($np){
                      $bc->whereIn('BC_CODE', $np)
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'pending-in':
              $query->whereNotNull('PLP_SETUJU_DATE')
                    ->whereNull('SCAN_IN_DATE')
                    ->with(['master.latestPlp', 'bclog' => function($bc) use ($np){
                      $bc->whereIn('BC_CODE', $np)
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'sppb':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->where(function($q){
                      $q->whereNotNull('SPPBNumber')
                        ->orWhereIn('BC_CODE', [401,403,404, 405]);
                    })
                    ->with(['master.latestPlp', 'bclog' => function($bc){
                      $bc->whereIn('BC_CODE', [401,403,404, 405])
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'pending-x-ray':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->whereIn('BC_CODE', [501,502,503,504])
                    ->with('master.latestPlp');
              break;
            case 'pending-in-tms':
              $query->whereNotNull('ShipmentNumber')
                    ->whereNotNull('SCAN_IN')
                    ->whereNull('CW_Ref_GateIn');
              break;
            case 'pending-out-tms':
              $query->whereNotNull('ShipmentNumber')
                    ->whereNotNull('SCAN_OUT')
                    ->whereNull('CW_Ref_GateOut');
              break;
            case 'delivered':
              $query->whereNotNull('SCAN_OUT')
                    ->with('master.latestPlp');
              break;
            case 'periksa-fisik':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where(function($h){
                      $h->whereIn('BC_CODE', [307,205])
                        ->orWhere(function($hb) {
                          $hb->whereHas('bclog', function($bl){
                            return $bl->where('BC_CODE', 307);
                          })
                          ->whereDoesntHave('bclog', function($blt){
                            return $blt->where('BC_CODE', 206);
                          })
                          ->whereNotIn('BC_CODE', [307,205,401,403,404,408]);
                        });
                    })
                    ->with(['master.latestPlp', 'bclog' => function($bc){
                      $bc->where('BC_CODE', 307)
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'npd':
              $query->where('BC_CODE', 305)
                    ->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->with(['master.latestPlp', 'bclog' => function($bc){
                      $bc->where('BC_CODE', 305)                      
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'skipcn':
              $query->whereNull('BC_CODE')
                    ->whereNull('BC_201')
                    ->where('SKIP', 'Y')
                    ->whereIn('JNS_AJU', [1,2])
                    ->with('master.latestPlp');
              break;
            case 'current-now':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->with(['master.latestPlp', 'bclog' => function($bc) use ($np){
                      $bc->whereIn('BC_CODE', $np)
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            case 'abandon':
              $query->whereNull('SCAN_OUT')
                    ->whereNotNull('SCAN_IN')
                    ->where('SCAN_IN_DATE', '<', $abandonDate)                    
                    ->with(['master.latestPlp', 'bclog' => function($bc) use ($np){
                      $bc->whereIn('BC_CODE', $np)
                        ->select('LogID', 'HouseID', 'BC_CODE');
                    }]);
              break;
            default:
              $query->whereNull('id');
              break;
          }
          
          $query->whereIn($column, $br);

          if($request->tipe == 'xls'){
            return Excel::download(new DashboardExport($query), 'inventory.xlsx');
          } elseif ( $request->tipe == 'pdf' ){
            $tipe = 'pdf';
            $data = $query->limit(100)->get();

            $pdf = PDF::setOption([
              'enable_php' => true,
            ]);

            $pdf->loadView('exports.dashboard', compact(['data', 'tipe']));

            return $pdf->setPaper('LEGAL', 'landscape')->stream();
          }

          if($request->order[0]['column'] == 0)
          {
            $query->latest($lt)->orderBy($srt);
          }          

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->filterColumn('NO_MASTER_BLAWB', function($query, $keyword) use ($srt) {
                              $search = str_replace('-', '', $keyword);
                              if($srt == 'MAWBNumber'){
                                $query->whereRaw("REPLACE(MAWBNumber, ' ', '') LIKE '%$search%'");
                              } else {
                                $query->whereRaw("REPLACE(NO_MASTER_BLAWB, ' ', '') LIKE '%$search%'");
                              }

                          })
                          ->filterColumn('NO_BARANG', function($query, $keyword) use($srt) {
                              $search = $keyword;
                              if($srt == 'MAWBNumber'){
                                $query->whereHas('houses', function($h) use ($search){
                                  return $h->where('NO_BARANG', $search);
                                });
                              } else {
                                $query->where('NO_BARANG', $search);
                              }

                          })
                          ->editColumn('NO_MASTER_BLAWB', function($row) use($cl){
                            $mawb = $row->mawb_parse;
                            $idm = ($row->MAWBNumber) ? $row->id : $row->MasterID;

                            $btn = '<a href="'.route('manifest.consolidations.show', ['consolidation' => Crypt::encrypt($idm)]).'"  target="_blank">'.$mawb.'</a>';

                            $data = [
                              'display' => $btn,
                              'search' => $mawb
                            ];

                            return $btn;
                          })
                          ->editColumn('NO_BARANG', function($row){
                            if($row->MAWBNumber){
                              $btn = $row->NO_BARANG . ' Houses';
                            } else {
                              $btn = '<a href="'.route('manifest.shipments.show', ['shipment' => Crypt::encrypt($row->id)]).'" target="_blank">'.$row->NO_BARANG.'</a>';
                            }

                            return $btn;
                          })
                          ->editColumn('NM_PENGIRIM', function($row){
                            return $row->NM_PENGIRIM ?? "-";
                          })
                          ->editColumn('NM_PENERIMA', function($row){
                            return $row->NM_PENERIMA ?? "-";
                          })
                          ->editColumn('BC_CODE', function($row) use ($np){
                            $code = ($row->JNS_AJU && !in_array($row->JNS_AJU, [1,2])) ? 'SPPB' : $row->BC_CODE;
                            $url = $code;
                            if(in_array($code, $np))
                            {
                              $bc = $row->bclog->where('BC_CODE', $code)->first();
                              if($bc){
                                $url ='<a href="'.route('logs.cetak').'?id='.\Crypt::encrypt($bc->LogID).'"
                                            target="_blank">'.$code.'</a>';
                              }
                            }

                            return $url;
                          })
                          ->editColumn('SPPBNumber', function($row){
                            return $row->SPPBNumber ?? "-";
                          })
                          ->editColumn('TPS_GateInREF', function($row){
                            return $row->TPS_GateInREF ?? "-";
                          })
                          ->editColumn('TPS_GateOutREF', function($row){
                            return $row->TPS_GateOutREF ?? "-";
                          })
                          ->addColumn('PLP', function($row){
                            return $row->PLPNumber ?? $row->master->PLPNumber ?? "-";
                          })
                          ->editColumn('BC_DATE', function($row){
                            return $row->BC_DATE ?? NULL;
                          })
                          ->editColumn('SCAN_IN_DATE', function($row){
                            return $row->SCAN_IN_DATE ?? NULL;
                          })
                          ->editColumn('ExitDate', function($row){
                              return $row->ExitDate ?? NULL;
                          }) 
                          ->editColumn('ExitTime', function($row){
                              return $row->ExitTime ?? NULL;
                          }) 
                          ->addColumn('actions', function($row) use ($jenis, $user){
                            if($row->MAWBNumber)
                            {
                              $latestPlp = $row->latestPlp?->first();
                            } else {
                              $latestPlp = $row->master?->latestPlp->first();
                            }
                            
                            $btn = '';
                            $url = '';
                            $btnName = '';
                            $judul = '';
                            $toggle = '';
                            $target = '';
                            $for = '';
                            $method = '';
                            $parameter = '';
                            $bg = 'btn-primary';

                            switch ($jenis) {
                              case 'pending-plp':
                                if($latestPlp
                                    && $latestPlp->pengajuan == true){
                                  $btnName = 'Get PLP Response';
                                  $parameter = 'plp-response';
                                } else{
                                  $bg = 'btn-warning';
                                  $btnName = 'Get Response Batal';
                                  $parameter = 'plp-resbatal';
                                }
                                if($user->can('edit_dashboard')){
                                  $url = route('manifest.plp', ['master' => Crypt::encrypt($row->id)]);                                  
                                }
                                $judul = 'Waiting Approval PLP';
                                $method = 'POST';
                                break;
                              case 'pending-in-tms':
                                if($user->can('edit_dashboard')){
                                  $url = route('scheduler', [
                                                            'id' => $row->id
                                                          ]);
                                }                                
                                $btnName = 'Send to One TMS';
                                $method = 'GET';
                                $parameter = 'ftpin';
                                break;
                              case 'pending-out-tms':
                                if($user->can('edit_dashboard')){
                                  $url = route('scheduler', [
                                                            'id' => $row->id
                                                          ]);
                                }                               
                                $btnName = 'Send to One TMS';
                                $method = 'GET';
                                $parameter = 'ftpout';
                                break;
                              case 'pending-in-wo-plp':
                                if($latestPlp
                                    && $latestPlp->STATUS == 'Pending'){
                                  if($latestPlp->pengajuan == true){
                                    $btnName = 'Get PLP Response';
                                    $parameter = 'plp-response';
                                  } else{
                                    $bg = 'btn-warning';
                                    $btnName = 'Get Response Batal';
                                    $parameter = 'plp-resbatal';
                                  }
                                  if($user->can('edit_dashboard')){
                                    $url = route('manifest.plp', ['master' => Crypt::encrypt($row->id)]);
                                  }                                  
                                  $method = 'POST';                                  
                                } else {
                                  if($user->can('edit_dashboard')){
                                    $url = route('manifest.plp', [
                                                'master' => \Crypt::encrypt($row->id)
                                              ]);
                                  }                                  
                                  $btnName = 'Request PLP';
                                  $judul = 'Pending Gate In Without PLP';
                                  $method = 'POST';
                                  $parameter = 'plp-request';
                                }
                                
                                break;
                              default:
                                # code...
                                break;
                            }

                            if($url != ''){
                              $btn = '<button data-href="'.$url.'" 
                                              data-id="'.$row->id.'"
                                              data-method="'.$method.'"
                                              data-jenis="'.$jenis.'"
                                              data-judul="'.$judul.'"
                                              data-parameter="'.$parameter.'"';

                              if($toggle != ''){
                                $btn .= 'data-toggle="'.$toggle.'"
                                        data-target="'.$target.'"';
                              }
                              if($for != ''){
                                $btn .= ' data-untuk="'.$for.'"';
                              }

                              $btn .= 'class="btn btn-xs '.$bg.' elevation-2 actions">'.$btnName.'</button>';

                            }

                            $btn .= '<button type="button" 
                                      class="btn btn-xs btn-success dropdown-toggle dropdown-icon mx-1" 
                                      data-toggle="dropdown">
                                        <i class="fa fa-print"></i>
                                    </button>
                                    <div class="dropdown-menu">';
                                    if(!$row->MAWBNumber)
                                    {
                                      $btn .= '<a href="'.route('download.manifest.label', ['house' => Crypt::encrypt($row->id)]).'"
                                                class="dropdown-item" 
                                                target="_blank">Label</a>';
                                    } else {
                                      $btn .= '<button data-href="'.route('manifest.consolidations.update', ['consolidation' => Crypt::encrypt($row->id)]).'"
                                                class="dropdown-item printlabel" 
                                                target="_blank">Label</button>';
                                    }

                            if($row->SCAN_OUT_DATE){
                              $btn .= '<a href="'.route('download.manifest.shipments').'?shipment='.$row->id.'&header=1"
                                          class="dropdown-item" 
                                          target="_blank">DO With Header</a>
                                        <a href="'.route('download.manifest.shipments').'?shipment='.$row->id.'&header=0"
                                          class="dropdown-item" 
                                          target="_blank">DO Without Header</a>';
                            }
                            if($row->SCAN_IN_DATE){
                              $btn .= '<button type="button"
                                          data-href="'.route('scheduler', ['jenis' => 'gatein', 'id' => $row->id]).'"
                                          class="dropdown-item resend" 
                                          target="_blank">Resend Gate In</button>';
                            }
                            if($row->SCAN_OUT_DATE){
                              $btn .= '<button type="button"
                                        data-href="'.route('scheduler', ['jenis' => 'gateout', 'id' => $row->id]).'"
                                        class="dropdown-item resend" 
                                        target="_blank">Resend Gate Out</button>';
                            }
                            
                            if($latestPlp
                                && $latestPlp->STATUS != 'Pending'){
                              $btn .= ' <a href="'.route('plp.print', ['plp' => $latestPlp->id]).'"
                              class="dropdown-item" 
                              target="_blank">Print PLP</a>';
                            }

                            $btn .= '</div>';

                            return $btn;
                          })
                          ->rawColumns(['NO_MASTER_BLAWB', 'NO_BARANG', 'BC_CODE', 'actions'])
                          ->toJson();
        // }
    }

    public function calculatePlp(Request $request, $brid)
    {
        $pendingPlp = Master::whereHas('plponline', function($q) use($brid){
                              return $q->where('pengajuan', true)
                                      ->where('STATUS', 'Pending')
                                      ->whereIn('CABANG', $brid);
                            })
                            ->where('ArrivalDate', '>', '2024-01-01')
                            ->count();

        $pendingInWoPlp = Master::whereNull('PLPNumber')
                                ->whereDoesntHave('plponline')
                                ->whereIn('mBRANCH', $brid)
                                ->where('ArrivalDate', '>', '2024-01-01')
                                ->count();

        $pendingInPlp = Master::whereNotNull('PLPNumber')
                                // ->whereDoesntHave('plponline')
                                ->whereDoesntHave('houses', function($h){
                                  return $h->whereNotNull('SCAN_IN_DATE');
                                })
                                ->whereIn('mBRANCH', $brid)
                                ->where('ArrivalDate', '>', '2024-01-01')
                                ->count();

        return response()->json(compact(['pendingPlp', 'pendingInWoPlp', 'pendingInPlp']));
    }

    public function calculateCurrent(Request $request, $brid, $abandonDate)
    {        
        $current = House::whereNotNull('SCAN_IN')
                        ->whereNull('SCAN_OUT')
                        ->where('SCAN_IN_DATE', '>=', $abandonDate)
                        ->whereIn('BRANCH', $brid)
                        ->count();
        return response()->json(compact(['current']));
    }

    public function calculateAbandon(Request $request, $brid, $abandonDate)
    {       
        $abandon = House::whereNull('SCAN_OUT')
                        ->whereNotNull('SCAN_IN')
                        ->where('SCAN_IN_DATE', '<', $abandonDate)
                        ->whereIn('BRANCH', $brid)
                        ->count();
        return response()->json(compact(['abandon']));
    }

    public function calculateState(Request $request, $brid, $abandonDate)
    {
        $pendingScanIn = House::whereNotNull('PLP_SETUJU_DATE')
                              ->whereNull('SCAN_IN_DATE')
                              ->whereIn('BRANCH', $brid)
                              ->count();

        $pendingSppb = House::whereNotNull('SCAN_IN')
                            ->whereNull('SCAN_OUT')
                            ->where('SCAN_IN_DATE', '>=', $abandonDate)
                            ->where(function($q){
                              $q->whereNull('SPPBNumber')
                                ->orWhereNotIn('BC_CODE', [401,403,404,408]);
                            })
                            // ->whereNull('SPPBNumber')
                            ->whereIn('BRANCH', $brid)
                            ->count();

        $sppb = House::whereNotNull('SCAN_IN')
                      ->whereNull('SCAN_OUT')
                      ->where('SCAN_IN_DATE', '>=', $abandonDate)
                      ->where(function($q){
                      $q->whereNotNull('SPPBNumber')
                        ->orWhereIn('BC_CODE', [401,403,404]);
                      })
                      ->whereIn('BRANCH', $brid)
                      ->count();

        $pendingXray = House::whereNotNull('SCAN_IN')
                            ->whereNull('SCAN_OUT')
                            ->where('SCAN_IN_DATE', '>=', $abandonDate)
                            ->whereIn('BC_CODE', [501,502,503,504])
                            ->whereIn('BRANCH', $brid)
                            ->count();

        return response()->json(compact(['pendingScanIn','pendingSppb','sppb','pendingXray']));
    }

    public function calculateOther(Request $request, $brid)
    {        
        $periksaFisik = House::whereNotNull('SCAN_IN')
                              ->whereNull('SCAN_OUT')
                              ->where(function($h){
                                $h->whereIn('BC_CODE', [307,205])
                                  ->orWhere(function($hb) {
                                    $hb->whereHas('bclog', function($bl){
                                      return $bl->where('BC_CODE', 307);
                                    })
                                    ->whereDoesntHave('bclog', function($blt){
                                      return $blt->where('BC_CODE', 206);
                                    })
                                    ->whereNotIn('BC_CODE', [307,205,401,403,404,408]);
                                  });
                              })
                              ->whereIn('BRANCH', $brid)->count();
        $npd = House::where('BC_CODE', 305)
                    ->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->whereIn('BRANCH', $brid)->count();
        $skipcn = House::whereNull('BC_201')
                        ->whereNull('BC_CODE')
                        ->where('SKIP', 'Y')
                        ->whereIn('JNS_AJU', [1,2])
                        ->whereIn('BRANCH', $brid)->count();

        return response()->json(compact(['periksaFisik','npd', 'skipcn']));
    }

    public function calculateCompleted(Request $request, $brid)
    {
        $delivered = House::whereNotNull('SCAN_OUT')
                          ->whereIn('BRANCH', $brid)->count();

        return response()->json(compact(['delivered']));
    }
}
