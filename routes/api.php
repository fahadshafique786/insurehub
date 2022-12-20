<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClassesController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\QuotationController;
use App\Http\Controllers\API\PaymentMethodController;

/*
|--------------------------------------------------------------------------
| API RoutesAuthController
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!gg
|
*/

Route::middleware('auth:sanctum')->post('/user', function (Request $request) {
    return $request->user();
});

Route::get('home',[HomeController::class,'index'])->name('home');
Route::post('register',[AuthController::class,'register'])->name('register');
Route::post('login', [AuthController::class,'login'])->name('login');
Route::post('profile',[AuthController::class,'viewProfile'])->name('view.profile');

Route::get('classes',[ClassesController::class,'getClasses'])->name('get.classes');
Route::get('sub-classes',[ClassesController::class,'getSubClasses'])->name('sub-classes');
Route::post('sub-class-form',[ClassesController::class,'getSubClassFormAttributes'])->name('sub-class-form');
Route::post('get_child_form_field_values',[ClassesController::class,'getChildFormFieldValues']);

Route::get('get-vehicle',[ClassesController::class,'getVehicle'])->name('vehicle');
Route::get('vehicle-by-id',[ClassesController::class,'getVehicleByID'])->name('vehicle-by-id');

Route::post('product/list',[ProductController::class,'getProductsList'])->name('product-list');

Route::post('order/generate',[QuotationController::class,'GenerateOrder'])->name('order-generate');

Route::post('customer/quote-fields',[QuotationController::class,'GetQuotationFormFields'])->name('quote-fields');

Route::post('customer/store/quotation-additional-info',[QuotationController::class,'SaveQuotationAdditionalInfo'])->name('store-quote-info');

Route::post('get-payment-methods',[PaymentMethodController::class,'getAllPaymentMethods']);
