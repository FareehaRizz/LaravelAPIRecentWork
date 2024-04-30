<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\AddDeviceController;
use App\Http\Controllers\WarningController;


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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::group(['middleware'=>'api'],function($routes){
    Route::post('/register',[UserController:: class, 'register']);
    Route::post('/login',[UserController::class, 'login']);
    Route::post('/profile',[UserController::class, 'profile']);
    Route::post('/refresh',[UserController::class, 'refresh']);
    Route::post('/logout',[UserController::class, 'logout']);
    Route::get('/trends', [UserController::class, 'trends']);
    Route::post('/generate-report', [ReportController::class, 'generateReport']);
    Route::get('/warnings', [WarningController::class, 'warnings']);
    Route::get('/generate-bill', [BillController::class, 'generateBill']);
    Route::post('/add-device', [AddDeviceController::class, 'addDevice']);
    Route::post('/forgetPassword',[UserController::class, 'forgetPassword']);
Route::get('/liveMonitoring',[UserController::class, 'liveMonitoring']);
Route::post('/liveMonitoring',[UserController::class, 'liveMonitoring']);


});
