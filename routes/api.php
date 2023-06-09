<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventosApiController;
use App\Http\Controllers\API\AuthenticatedAPIController;
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

Route::get('eventosGet', [EventosApiController::class, 'eventosGet'])->name('api.eventosGet');

Route::post('login', [AuthenticatedAPIController::class, 'login'])->name('api.login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('logout', [AuthenticatedAPIController::class, 'logout'])->name('api.logout');
    
});
