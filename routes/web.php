<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController as ApiAuthCTRL;
use App\Http\Controllers\Api\AliExpressController as AliExpressCTRL;

Route::controller(AliExpressCTRL::class)
  // ->middleware(['auth:sanctum'])
  ->prefix('category')
  ->group(function () {

    // List categories 
    Route::get('list', 'index')->name('category.list');
});

Route::get('/', function(){
  return view('welcome_to_the_services', ['name' => 'James']);
});

// Route::get('/date-convert', function(){
  
//     $mdY = convertYmdToMdy('2024-03-27');
//     var_dump("Converted into 'MDY': " . $mdY);
    
//     $ymd = convertMdyToYmd('03-27-2024');
//     var_dump("Converted into 'YMD': " . $ymd);
// });