<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\Running;
use App\Helpers\SoapHelper;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/randomize', function(){
  $shipments = ['S00117299','S00120513','S00119068','S00120153','S00120150','S00117103','S00117300','S00116832','S00118522','S00113637','S00112446','S00118525','S00109316','S00109315','S00111845','S00117297','S00083632','S00117105'];

  $consols = ['C00081545','C00082162','C00080948','C00081834','C00081833','C00079487','C00081544','C00079268','C00080551','C00076768','C00075785','C00080554','C00073382','C00073381','C00075283','C00081548','C00055726','C00079489'];

  $hawb = ['IN101115858','VN100120513','12341313112','12209645','12209643','SG100117103','N1212653','CDG1234','SG100920627','GA131231311','US10056087','SG100920620','ARN12345','US100XXXX','EFGH123','SG100813840','IN100083632','JT131313111'];

  $mawb = ['08163294895','61829617081','12345678905','15757584100','17257584100','12689348000','17652661593','17258752783','12689250733','12675676252','17680809013','12689250730','17686288985','17623456020','60769863986','12689151311','15740125245','54231231350'];

  foreach ($shipments as $key => $shipment) {
    $cek = App\Models\House::where('ShipmentNumber', $shipment)->first();

    if(!$cek){
      $house = App\Models\House::inRandomOrder()->first();
    } else {
      $house = $cek;
    }   

    DB::beginTransaction();
    try {
      $house->update([
              'NO_MASTER_BLAWB' => $mawb[$key],
              'ShipmentNumber' => $shipment,
              'NO_HOUSE_BLAWB' => $hawb[$key],
              'NO_BARANG' => $hawb[$key],
              'SCAN_IN_DATE' => NULL,
              'SCAN_OUT_DATE' => NULL,
              'SCAN_IN' => NULL,
              'SCAN_OUT' => NULL,
              'ExitDate' => NULL,
              'ExitTime' => NULL,
            ]);
      $house->master->update([
                    'ConsolNumber' => $consols[$key],
                    'MAWBNumber' => $mawb[$key],
                  ]);

      DB::commit();
      
    } catch (\Throwable $th) {
      throw $th;
    }
  }
});

Route::get('/cek-koneksi', function(Request $request){
  $shipment = $request->shipment ?? 'S00117299';
  $time = now()->setTimeZone('UTC');
  $giwiaTxt = '<UniversalEvent xmlns="http://www.cargowise.com/Schemas/Universal/2011/11">
              <Event>
                  <DataContext>
                      <Company>
                          <Code>ID1</Code>
                      </Company>
                <EnterpriseID>B52</EnterpriseID>
                <ServerID>TS2</ServerID>
                      <DataTargetCollection>
                          <DataTarget>
                              <Type>ForwardingShipment</Type>
                              <Key>'.$shipment.'</Key>
                          </DataTarget>
                      </DataTargetCollection>
                  </DataContext>
                  <EventTime>'.$time->toDateTimeLocalString().'</EventTime>
                  <EventType>FUL</EventType>
                  <EventReference>|EXT_SOFTWARE=TPS|FAC=CFS|LNK=GIWIA|LOC=IDJKT|</EventReference>
                  <IsEstimate>false</IsEstimate>
              </Event>
          </UniversalEvent>
          ';
              
  $micro = $time->format('u');

  $giwiName = 'XUE_TPSID_'.$shipment.'_GIWIA_'.$time->format('YmdHms').substr($micro, 0,3).'_'.Str::uuid().'.xml';

  try {        

    $giwia = Storage::disk('sftp')->put($giwiName, $giwiaTxt);
    // $giwia = Storage::disk('ftp')->put($giwiName, $giwiaTxt);
    
    return $giwiName;

  } catch (FilesystemException | UnableToWriteFile $th) {
    
    // return redirect('/cek-koneksi')->withErrors($th->getMessage());
    throw $th;
  } 
});

Route::get('/gantipassword', function(){
 
  $sh = new SoapHelper;

  $soap = $sh->soap();
          
  $sResponse = $soap->GantiPassword(
    [      
      'UserName' => config('app.tps.user'),
      'Password' => config('app.tps.password'),
      'PasswordBaru' => "B0loR3L0g15TiK",
      'PasswordKonfirmasi' => "B0loR3L0g15TiK"
    ]);

  $response =  $soap->__getLastResponse();
  $request =  $soap->__getLastRequest();

  $status = $sh->getResults('GantiPasswordResult', $response);

  $log = \App\Models\SchedulerLog::create([
                                'logable_type' => null,
                                'logable_id' => null,
                                'process' => 'GantiPassword',
                                'request' => $request,
                                'response' => $response,
                                'info' => $status
                              ]);

                              return $status;
});

Route::view('/tes', 'scheduler', ['url' => '/tes']);

Route::get('/scheduler', 'SchedulerController@index')
      ->name('scheduler');

//Main Routing
Route::get('/', 'AuthController@index')->name('login');
Route::post('/', 'AuthController@postlogin');

//Route Group for Authenticated User
Route::group(['middleware' => 'auth'], function(){
  Route::get('/logout', 'AuthController@logout')->name('logout');//Logout
  Route::get('/dashboard', 'DashboardController@index')->name('dashboard');//Dashboard
  Route::get('/profile', 'AuthController@profile')->name('profile'); // Profile
  Route::put('/profile/{user}', 'AuthController@update')->name('profile.update'); // Profile

  Route::get('/global-search', 'DashboardController@search')
       ->name('global.search');
  Route::get('/global-download', 'DownloadController@download')
       ->name('global.download');

  //Super-Admin Routes
  Route::group(['middleware' => 'role:super-admin', 'as' => 'admin.'], function(){
    Route::get('/administrator', 'AdminDashboardController@index')->name('dashboard');
    Route::get('/administrator/profile', 'AuthController@profile')->name('profile');
    Route::put('/administrator/profile/{user}', 'AuthController@update')->name('profile.update');

    Route::get('/administrator/download', 'AdminDownloadController@download')
         ->name('download');
    
    try {

      $menu = \App\Models\Menu::with(['parent_items' => function ($q) {
                                  $q->where('active', true)
                                    ->orderBy('order', 'asc');
                              }, 'parent_items.children' => function($c){
                                $c->where('active', true)
                                  ->orderBy('order', 'asc');
                              }])
                              ->where('name', 'admin')
                              ->first();
      if($menu){
        foreach($menu->parent_items as $main){
          $mainUrl = $main->link();
          $mainTitle = Str::lower($main->title);
          $routeName = Str::replace(' ', '_', $mainTitle);
          $singular = Str::singular(Str::replace(' ','_', $mainTitle));
          if($mainTitle != 'dashboard'){           
            $ctName = Str::replace(' ', '', Str::title($mainTitle));

            Route::get($mainUrl, 'Admin'. $ctName .'Controller@index')->name($routeName);
            Route::get($mainUrl.'/create', 'Admin'. $ctName .'Controller@create')->name($routeName.'.create');
            Route::post($mainUrl, 'Admin'. $ctName .'Controller@store')->name($routeName.'.store');
            Route::get($mainUrl.'/{'. $singular .'}', 'Admin'. $ctName .'Controller@show')->name($routeName.'.show');           
            Route::get($mainUrl.'/{'. $singular .'}/edit', 'Admin'. $ctName .'Controller@edit')->name($routeName.'.edit');
            Route::put($mainUrl.'/{'. $singular .'}', 'Admin'. $ctName .'Controller@update')->name($routeName.'.update');
            Route::delete($mainUrl.'/{'. $singular .'}', 'Admin'. $ctName .'Controller@destroy')->name($routeName.'.delete'); 
            
            if($mainTitle == 'menus'){
              Route::get($mainUrl.'/{'. $singular .'}/builder', 'Admin'. $ctName .'BuilderController@index')->name($routeName.'.builder');
              Route::post($mainUrl.'/{'. $singular .'}/builder', 'Admin'. $ctName .'BuilderController@store')->name($routeName.'.builder.store');
              Route::put($mainUrl.'/{'. $singular .'}/builder/{id}', 'Admin'.$ctName .'BuilderController@update')->name($routeName.'.builder.update');
              Route::delete($mainUrl.'/{'. $singular .'}/builder/{id}', 'Admin'.$ctName .'BuilderController@destroy')->name($routeName.'.builder.destroy');

              Route::post($mainUrl.'/order', 'Admin'. $ctName .'BuilderController@order_item')->name($routeName.'.order');
            }
            
            if($mainTitle == 'companies'){
              Route::get($mainUrl .'/{'. $singular . '}/branches', 'Admin'. $ctName .'BranchesController@index')
                   ->name($routeName.'.branches.index');
              Route::get($mainUrl .'/{'. $singular . '}/branches/create', 'Admin'. $ctName .'BranchesController@create')
                   ->name($routeName.'.branches.create');
              Route::post($mainUrl.'/{'. $singular.'}/branches', 'Admin'.$ctName.'BranchesController@store')
                   ->name($routeName.'.branches.store');
              Route::get($mainUrl.'/{'. $singular.'}/branches/{branch}', 'Admin'.$ctName.'BranchesController@show')
                   ->name($routeName.'.branches.show');
              Route::get($mainUrl.'/{'. $singular.'}/branches/{branch}/edit', 'Admin'.$ctName.'BranchesController@edit')
                   ->name($routeName.'.branches.edit');
              Route::put($mainUrl.'/{'. $singular.'}/branches/{branch}', 'Admin'.$ctName.'BranchesController@update')
                   ->name($routeName.'.branches.update');
              Route::delete($mainUrl.'/{'. $singular.'}/branches/{branch}', 'Admin'.$ctName.'BranchesController@destroy')
                   ->name($routeName.'.branches.destroy');
            }
  
          }
        }
      }     
        
    } catch (\Throwable $th) {
      // throw $th;
    }
  
  });
  //End Super-Admin Routes

  //Menu Routes
  try {
    
    $menus = \App\Models\MenuItem::where('menu_id', '<>', 1)
                                 ->where('active', true)
                                 ->get();

    if($menus){
      foreach($menus as $menu){
        $mainUrl = $menu->link();
        $mainTitle = Str::lower($menu->title);
        $singular = ($menu->var_name) ? $menu->var_name : Str::singular(Str::replace(['-',' '],'_', $mainTitle));
        $routeName = Str::replace('/','.',ltrim($mainUrl,'/'));
        $permit = Str::replace(['.', '-'], '_', $routeName);
        
        ($menu->permission != '') 
              ? $middle = "can:$menu->permission" 
              : $middle = 'auth';        

        if($mainUrl != '#' && $menu->controller != ''){
          $cont = $menu->controller;
          Route::get($mainUrl, $cont .'Controller@index')
                ->name($routeName)
                ->middleware($middle);

          Route::get($mainUrl.'/create', $cont .'Controller@create')
                ->name($routeName.'.create')
                ->middleware('can:create_'.$permit);

          Route::post($mainUrl, $cont .'Controller@store')
                ->name($routeName.'.store')
                ->middleware('can:create_'.$permit);

          Route::get($mainUrl.'/{'. $singular .'}', $cont .'Controller@show')
                ->name($routeName.'.show')
                ->middleware('can:view_'.$permit);

          Route::get($mainUrl.'/{'. $singular .'}/edit', $cont .'Controller@edit')
                ->name($routeName.'.edit')
                ->middleware('can:edit_'.$permit);

          Route::put($mainUrl.'/{'. $singular .'}', $cont .'Controller@update')
                ->name($routeName.'.update')
                ->middleware('can:edit_'.$permit);

          Route::delete($mainUrl.'/{'. $singular .'}', $cont .'Controller@destroy')
                ->name($routeName.'.delete')
                ->middleware('can:delete_'.$permit);

          //Select2 Route
          Route::get('/select2'.$mainUrl, $cont . 'Controller@select2')
               ->name('select2.'.$routeName);
          //Download Route
          Route::get('/download'.$mainUrl, $cont . 'Controller@download')
               ->name('download.'.$routeName);
          //Upload Route
          Route::post('/upload'.$mainUrl, $cont . 'Controller@upload')
               ->name('upload.'.$routeName);

        } elseif($mainUrl != '#' && $menu->controller == ''){

          $page = getPage('pages.'.$mainTitle.'.index');

          Route::get($mainUrl, function() use ($page){
            return view($page);
          })->middleware($middle);

        }

      }
    }     
      
  } catch (\Throwable $th) {

    // throw $th;
    
  }
  //End Menu Routes  
});
