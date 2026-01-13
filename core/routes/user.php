<?php

use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->group(function () {

    Route::controller('LoginController')->group(function(){
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->name('logout');
    });

    Route::controller('RegisterController')->group(function(){
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register')->middleware('registration.status');
        Route::post('check-mail', 'checkUser')->name('checkUser');
    });

    Route::controller('ForgotPasswordController')->group(function(){
        Route::get('password/reset', 'showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
        Route::get('password/code-verify', 'codeVerify')->name('password.code.verify');
        Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
    });
    Route::controller('ResetPasswordController')->group(function(){
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });
});

Route::middleware('auth')->name('user.')->group(function () {
    //authorization
    Route::namespace('User')->controller('AuthorizationController')->group(function(){
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
    });

    Route::middleware(['check.status'])->group(function () {

        Route::get('user-data', 'User\UserController@userData')->name('data');
        Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

        Route::middleware('registration.complete')->namespace('User')->group(function () {

            Route::controller('UserController')->group(function(){
                Route::get('dashboard', 'home')->name('home');

                //2FA
                Route::get('twofactor', 'show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('kyc-form','kycForm')->name('kyc.form');
                Route::get('kyc-data','kycData')->name('kyc.data');
                Route::post('kyc-submit','kycSubmit')->name('kyc.submit');

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                Route::get('transactions','transactions')->name('transactions');
                Route::get('commissions', 'commissions')->name('commissions');
                Route::get('referred-users', 'referredUsers')->name('referred');
                Route::get('premium-commissions', 'premiumCommissions')->name('premium.commissions');
                Route::get('attachment-download/{fil_hash}','attachmentDownload')->name('attachment.download');

                //Plans
                Route::post('plans/buy','buyPlan')->name('buyPlan');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function(){
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });


            // Withdraw - Direct withdrawal with PIN (no preview step)
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function(){
                Route::middleware('kyc')->group(function(){
                    Route::get('/', 'withdrawMoney');
                    Route::post('/', 'withdrawStore')->name('.money');
                });
                Route::get('history', 'withdrawLog')->name('.history');
            });

            //PTC
            Route::controller('PtcController')->name('ptc.')->prefix('ptc')->group(function(){
                Route::get('/','index')->name('index');
                Route::get('show/{hash}','show')->name('show');
                Route::post('confirm/{hash}','confirm')->name('confirm');
                Route::get('my-ads','ads')->name('ads');
                Route::get('create','create')->name('create');
                Route::post('store','store')->name('store');
                Route::get('edit/{id}','edit')->name('edit');
                Route::post('update/{id}','update')->name('update');
                Route::get('status/{id}','status')->name('status');
                Route::get('clicks','clicks')->name('clicks');
            });

            // Hold Wallet Routes (with achievement-based transfer rules)
            Route::controller('HoldWalletController')->prefix('hold-wallet')->name('hold.wallet')->group(function(){
                Route::get('/', 'index');
                Route::post('/transfer', 'transfer')->name('.transfer');
                Route::get('/achievement-info', 'getAchievementInfo')->name('.achievement.info');
                Route::get('/transfer-history', 'getTransferHistory')->name('.transfer.history');
            });

            // New Features Routes
            Route::controller('UserController')->group(function(){
                // Wallet Overview
                Route::get('wallet', 'wallet')->name('wallet');

                // Notifications
                Route::get('notifications', 'notifications')->name('notifications');
                Route::post('notifications/mark-read', 'markNotificationsRead')->name('notifications.mark.read');

                // Video Tutorials
                Route::get('video-tutorials', 'videoTutorials')->name('video.tutorials');
                Route::get('video-tutorial/{id}', 'videoTutorialView')->name('video.tutorial.view');

                // FAQ
                Route::get('faq', 'faq')->name('faq');

                // Daily Spotlights
                Route::get('spotlights', 'spotlights')->name('spotlights');
            });

            // Red Bag Routes
            Route::controller('RedBagController')->name('red-bag.')->prefix('red-bag')->group(function(){
                Route::get('/check', 'checkAvailability')->name('check');
                Route::post('/claim', 'claim')->name('claim');
                Route::get('/history', 'history')->name('history');
                Route::get('/stats', 'getStats')->name('stats');
            });

        });

        // Payment
        Route::middleware('registration.complete')->controller('Gateway\PaymentController')->group(function(){
            Route::any('/deposit', 'deposit')->name('deposit');
            Route::post('deposit/insert', 'depositInsert')->name('deposit.insert');
            Route::get('deposit/confirm', 'depositConfirm')->name('deposit.confirm');
            Route::get('deposit/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
            Route::post('deposit/manual', 'manualDepositUpdate')->name('deposit.manual.update');
        });
    });
});
