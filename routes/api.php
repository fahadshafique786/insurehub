<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClassesController;
use App\Http\Controllers\API\HomeController;

/*
|--------------------------------------------------------------------------
| API RoutesAuthController
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->post('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[AuthController::class,'register'])->name('register');
Route::post('login', [AuthController::class,'login'])->name('login');
Route::post('view-profile',[AuthController::class,'viewProfile'])->name('view.prfile');
Route::get('get_classes',[ClassesController::class,'getClasses'])->name('get.classes');
Route::get('home',[HomeController::class,'index'])->name('home');
Route::get('sub-classess',[ClassesController::class,'subClasses'])->name('subClasses');
Route::get('get-vehicle',[ClassesController::class,'getVehicle'])->name('vehicle');
Route::get('vehicle-by-id',[ClassesController::class,'getVehicleByID'])->name('vehicle-by-id');
