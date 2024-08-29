<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/editsequence', 'AdminRunningCodesController@editsequence')
     ->name('admin.editsequence');
Route::get('/dashboard-shipment', 'DashboardController@dashboardShipment')
     ->name('dashboard.shipment');
Route::post('/editplp', 'PlpController@update')
     ->name('plp.edit');
Route::post('/please/encrypt/this', 'DashboardController@encrypt')
      ->name('crypt.this');
