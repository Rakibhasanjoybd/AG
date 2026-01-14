<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Ptc;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // CRITICAL (performance/SEO): Debugbar must never render in production.
        // We avoid a hard dependency on the facade/alias and just disable the bound service if present.
        if (app()->environment('production')) {
            try {
                if (app()->bound('debugbar')) {
                    app('debugbar')->disable();
                }
            } catch (Throwable $e) {
                // No-op: debugbar may not be installed in some deployments.
            }
        }

        $general = gs();
        $viewShare['general'] = $general;
        $activeTemplate = activeTemplate();
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        try {
            $viewShare['language'] = Language::all();
        } catch (Throwable $e) {
            $viewShare['language'] = collect(); // Empty collection if table doesn't exist
        }
        $viewShare['emptyMessage'] = 'Data not found';
        view()->share($viewShare);


        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'bannedUsersCount'           => User::banned()->count(),
                'emailUnverifiedUsersCount' => User::emailUnverified()->count(),
                'mobileUnverifiedUsersCount'   => User::mobileUnverified()->count(),
                'kycUnverifiedUsersCount'   => User::kycUnverified()->count(),
                'kycPendingUsersCount'   => User::kycPending()->count(),
                'pendingTicketCount'         => SupportTicket::whereIN('status', [0,2])->count(),
                'pendingDepositsCount'    => Deposit::pending()->count(),
                'pendingWithdrawCount'    => Withdrawal::pending()->count(),
                'pendingPtcCount'    => Ptc::pending()->count(),
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications'=>AdminNotification::where('read_status',0)->with('user')->orderBy('id','desc')->take(10)->get(),
                'adminNotificationCount'=>AdminNotification::where('read_status',0)->count(),
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if($general->force_ssl){
            URL::forceScheme('https');
        }


        Paginator::useBootstrapFour();
    }
}
