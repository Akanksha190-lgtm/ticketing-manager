<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

//------------------------ Authentication routes--------------------//
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

//------------------------ Protected routes - require authentication--------------------//
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    

    //------------------------ Ticketing Manager routes--------------------//

    Route::middleware('role:ticketing_manager')->group(function () {
        // Manager page
        Route::get('/ticketing', [App\Http\Controllers\FareCommissionEntriesController::class, 'index'])->name('ticketing.index');

        //fare commission entries routes
        Route::post('/fare-commission-entries', [App\Http\Controllers\FareCommissionEntriesController::class, 'store'])->name('fare-commission-entries.store');
        Route::put('/fare-commission-entries/{id}',[App\Http\Controllers\FareCommissionEntriesController::class, 'update']
        )->name('fare-commission-entries.update');
        Route::delete('/fare-commission-entries/{id}',[App\Http\Controllers\FareCommissionEntriesController::class, 'destroy'])->name('fare-commission-entries.destroy');

        //air commission master routes
        Route::put('/airline-commissions/{id}',[App\Http\Controllers\CommissionMasterController::class, 'updateCommission'])->name('airline-commissions.update');
        Route::delete('/airline-commissions/{id}',[App\Http\Controllers\CommissionMasterController::class, 'destroyCommission'])->name('airline-commissions.destroy');

        //route code
        Route::put('/routes/{id}/codes',[App\Http\Controllers\RouteController::class, 'updateCodes'])->name('routes.codes.update');
    });

    //------------------------ Ticketing Team routes--------------------//
    Route::middleware('role:ticketing_manager,ticketing_team')->group(function () {
        Route::get('/ticketing-team',[App\Http\Controllers\FareCommissionEntriesController::class, 'ticketingTeam'])->name('ticketing.team');
        Route::get('/fare-commission-entries/{id}/history',[App\Http\Controllers\FareCommissionEntriesController::class, 'history'])->name('fare-commission-entries.history');

    });
});