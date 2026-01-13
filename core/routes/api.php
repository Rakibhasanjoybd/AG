<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WithdrawController;

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

Route::namespace('Api')->name('api.')->group(function () {

    // =========================================================================
    // PUBLIC ROUTES (No authentication required)
    // =========================================================================
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('password/email', 'AuthController@forgotPassword')->name('password.email');
    Route::post('password/verify-code', 'AuthController@verifyCode')->name('password.verify');
    Route::post('password/reset', 'AuthController@resetPassword')->name('password.reset');

    // =========================================================================
    // PROTECTED ROUTES (All user actions require authentication)
    // This ensures any new endpoints added here are secure by default
    // =========================================================================
    Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
        
        // User Profile & Data
        Route::get('user', 'UserController@userInfo')->name('user.info');
        Route::post('user/data-submit', 'UserController@userDataSubmit')->name('user.data.submit');
        Route::post('user/profile-update', 'UserController@profileUpdate')->name('user.profile.update');
        Route::post('user/password-update', 'UserController@passwordUpdate')->name('user.password.update');
        
        // Deposits
        Route::get('deposit/methods', 'DepositController@methods')->name('deposit.methods');
        Route::post('deposit/submit', 'DepositController@submit')->name('deposit.submit');
        Route::get('deposit/history', 'DepositController@history')->name('deposit.history');
        
        // Withdrawals
        Route::get('withdraw/methods', 'WithdrawController@methods')->name('withdraw.methods');
        Route::post('withdraw/request', 'WithdrawController@withdrawStore')->name('withdraw.store');
        Route::get('withdraw/history', 'WithdrawController@history')->name('withdraw.history');
        
        // Plans
        Route::get('plans', 'PlanController@plans')->name('plans');
        Route::post('plans/buy', 'PlanController@buyPlan')->name('plans.buy');
        
        // Transactions
        Route::get('transactions', 'UserController@transactions')->name('transactions');
        
        // Red Bag API Routes
        Route::prefix('red-bag')->name('red-bag.')->group(function () {
            Route::get('/check', 'RedBagController@checkAvailability')->name('check');
            Route::post('/claim', 'RedBagController@claim')->name('claim');
            Route::get('/history', 'RedBagController@history')->name('history');
            Route::get('/stats', 'RedBagController@stats')->name('stats');
        });
        
        // Logout
        Route::post('logout', 'AuthController@logout')->name('logout');
    });
});
