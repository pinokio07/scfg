<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\DashboardExport;
use App\Models\User;
use App\Models\Master;
use App\Models\House;
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
        $today = today();
        $diff = 0;

        if($user->passLog->isEmpty()
            && $user->cannot('bypass-password')){
          return redirect('/profile');
        }
        
        $log = $user->lastLog;

        if($log){
          $created = $log->created_at;
          $diff = $created->diffInDays($today);
        }

        if($user->hasExactRoles('TPS Team')){
          if($diff > 76){
            return redirect('/profile');
          }
          return redirect()->route('tps-online.scan-in');
        }

        if($user->can('open_dashboard')){
          return $this->dashboardShipment($request, $diff);
        }

        return view('pages.default', compact(['diff']));
      
    }

    public function dashboardShipment(Request $request, int $diff = 0)
    {
        $today = today();
        $abandonDate = $today->subDays(30)->format('Y-m-d');
        
        if($request->ajax() || $request->tipe){
          $uid = \Crypt::decrypt($request->user);
          $user = User::findOrFail($uid);
          $jenis = $request->jenis;
          $query = House::query();

          switch ($jenis) {
            case 'pending-plp':
              $query->whereHas('master.plponline', function($q){
                                return $q->where('STATUS', 'Pending');
                              });
              break;
            case 'pending-in-wo-plp':
              $query->whereNull('PLP_SETUJU_DATE')
                    ->whereNull('SCAN_IN');
              break;
            case 'pending-in-plp':
              $query->whereNotNull('PLP_SETUJU_DATE')
                    ->whereNull('SCAN_IN')
                    ->with('master.latestPlp');
              break;
            case 'pending-sppb':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->whereNull('SPPBNumber')
                    ->with('master.latestPlp');
              break;
            case 'sppb':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->whereNotNull('SPPBNumber')
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
            case 'current-now':
              $query->whereNotNull('SCAN_IN')
                    ->whereNull('SCAN_OUT')
                    ->where('SCAN_IN_DATE', '>=', $abandonDate)
                    ->with('master.latestPlp');
              break;
            case 'abandon':
              $query->whereNull('SCAN_OUT')
                    ->whereNotNull('SCAN_IN')
                    ->where('SCAN_IN_DATE', '<', $abandonDate)                    
                    ->with('master.latestPlp');
              break;
            default:
              # code...
              break;
          }

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

          $query->latest('TGL_TIBA')->orderBy('NO_MASTER_BLAWB');

          return DataTables::eloquent($query)
                           ->addIndexColumn()
                           ->filterColumn('NO_MASTER_BLAWB', function($query, $keyword) {
                              $search = str_replace('-', '', $keyword);
                              $query->where('NO_MASTER_BLAWB', $search);
                            })
                           ->editColumn('NO_MASTER_BLAWB', function($row){
                            $mawb = $row->mawb_parse;

                            $btn = '<a href="'.route('manifest.consolidations.show', ['consolidation' => Crypt::encrypt($row->MasterID)]).'"  target="_blank">'.$mawb.'</a>';

                            $data = [
                              'display' => $btn,
                              'search' => $mawb
                            ];

                            return $btn;
                           })
                           ->editColumn('NO_BARANG', function($row){
                            $btn = '<a href="'.route('manifest.shipments.show', ['shipment' => Crypt::encrypt($row->id)]).'" target="_blank">'.$row->NO_BARANG.'</a>';

                            return $btn;
                           })
                           ->addColumn('PLP', function($row){
                            return $row->master->PLPNumber ?? "-";
                           })
                           ->addColumn('ArrivalDate', function($row){
                              if($row->master->ArrivalDate){
                                $time = Carbon::parse($row->master->ArrivalDate);
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
                           ->editColumn('SCAN_IN_DATE', function($row){
                              if($row->SCAN_IN_DATE){
                                $time = Carbon::parse($row->SCAN_IN_DATE);
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
                           ->editColumn('SCAN_IN_TIME', function($row){
                              if($row->SCAN_IN_DATE){
                                $time = Carbon::parse($row->SCAN_IN_DATE);
                                $display = $time->format('H:i:s');
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
                           ->editColumn('ExitDate', function($row){
                              if($row->ExitDate){
                                $time = Carbon::parse($row->ExitDate);
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
                           ->addColumn('ArrivalTime', function($row){
                              return $row->master->ArrivalTime;
                           })                           
                           ->addColumn('actions', function($row) use ($jenis, $user){
                            $latestPlp = $row->master->latestPlp->first();
                            $btn = '';
                            $url = '';
                            $btnName = '';
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
                                  $url = route('manifest.plp', ['master' => Crypt::encrypt($row->MasterID)]);                                  
                                }                                
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
                                    $url = route('manifest.plp', ['master' => Crypt::encrypt($row->MasterID)]);
                                  }                                  
                                  $method = 'POST';                                  
                                } else {
                                  if($user->can('edit_dashboard')){
                                    $url = route('manifest.plp', [
                                                'master' => \Crypt::encrypt($row->MasterID)
                                              ]);
                                  }                                  
                                  $btnName = 'Request PLP';
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
                                    <div class="dropdown-menu">
                                      <a href="'.route('download.manifest.label', ['house' => Crypt::encrypt($row->id)]).'"
                                      class="dropdown-item" 
                                      target="_blank">Label</a>';

                            if($row->SCAN_OUT_DATE){
                              $btn .= '<a href="'.route('download.manifest.shipments').'?shipment='.$row->id.'&header=1&type=do"
                                          class="dropdown-item" 
                                          target="_blank">Print DO</a>
                                        <a href="'.route('download.manifest.shipments').'?shipment='.$row->id.'&header=1&type=pod"
                                          class="dropdown-item" 
                                          target="_blank">Print POD</a>';
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
                           ->rawColumns(['NO_MASTER_BLAWB', 'NO_BARANG', 'actions'])
                           ->toJson();
        }

        $pendingPlp = PlpOnline::where('STATUS', 'Pending')->count();

        $pendingInWoPlp = House::whereNull('PLP_SETUJU_DATE')
                                ->whereNull('SCAN_IN')->count();

        $pendingInPlp = House::whereNotNull('PLP_SETUJU_DATE')
                             ->whereNull('SCAN_IN')->count();

        $pendingSppb = House::whereNotNull('SCAN_IN')
                            ->whereNull('SCAN_OUT')
                            ->where('SCAN_IN_DATE', '>=', $abandonDate)
                            ->whereNull('SPPBNumber')
                            ->count();

        $sppb = House::whereNotNull('SCAN_IN')
                      ->whereNull('SCAN_OUT')
                      ->where('SCAN_IN_DATE', '>=', $abandonDate)
                      ->whereNotNull('SPPBNumber')
                      ->count();

        // $pendingTmsIn = House::whereNotNull('SCAN_IN')
        //                       ->whereNull('CW_Ref_GateIn')
        //                       ->whereNotNull('ShipmentNumber')
        //                       ->count();

        // $pendingTmsOut = House::whereNotNull('SCAN_OUT')
        //                       ->whereNull('CW_Ref_GateOut')
        //                       ->whereNotNull('ShipmentNumber')
        //                       ->count();
        $pendingTmsIn = 0;
        $pendingTmsOut = 0;

        $delivered = House::whereNotNull('SCAN_OUT')->count();

        $current = House::whereNotNull('SCAN_IN')
                        ->whereNull('SCAN_OUT')
                        ->where('SCAN_IN_DATE', '>=', $abandonDate)
                        ->count();

        $abandon = House::whereNull('SCAN_OUT')
                        ->whereNotNull('SCAN_IN')
                        ->where('SCAN_IN_DATE', '<', $abandonDate)
                        ->count();

        $items = collect([
          'id' => 'id',
          'NO_MASTER_BLAWB' => 'Master BLAWB',
          'NO_BARANG' => 'House AWB',
          'ShipmentNumber' => 'Job File',
          'NM_PENERIMA' => 'Nama Penerima',
          'JML_BRG' => 'Package',
          'BRUTO' => 'Gross',
          'ChargeableWeight' => 'CW',
          'SPPBNumber' => 'No SPPB',
          'PLP' => 'No PLP',
          'ArrivalDate' => 'Tanggal Tiba',
          'SCAN_IN_DATE' => 'Gate In Date',
          'SCAN_IN_TIME' => 'Gate In Time',
          'ExitDate' => 'Exit Date',
          'ExitTime' => 'Exit Time',
          'TPS_GateInREF' => 'Gate In Ref',
          'TPS_GateOutREF' => 'Gate Out Ref',
          'actions' => 'Actions'
        ]);

        return view('pages.manifest.dashboard', compact(['items', 'pendingPlp', 'pendingInWoPlp', 'pendingInPlp', 'pendingSppb', 'sppb', 'pendingTmsIn', 'pendingTmsOut', 'delivered', 'current', 'abandon', 'diff']));
    }
}
