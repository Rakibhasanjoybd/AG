<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Http\Request;

class FreeUserController extends Controller
{
    /**
     * Display Free User System settings page
     */
    public function index()
    {
        $pageTitle = 'Free User System';
        $general = gs();

        // Get free user statistics
        $freeUserIds = User::where(function($q) {
            $q->whereNull('plan_id')
                ->orWhere('plan_id', 0)
                ->orWhere('expire_date', '<', now());
        })->pluck('id');

        $stats = [
            'total_free_users' => $freeUserIds->count(),
            'total_premium_users' => User::whereNotNull('plan_id')
                ->where('plan_id', '>', 0)
                ->where('expire_date', '>=', now())
                ->count(),
            'free_users_with_referrals' => User::whereIn('ref_by', $freeUserIds)->distinct('ref_by')->count('ref_by'),
        ];

        return view('admin.free_user_system', compact(
            'pageTitle',
            'general',
            'stats'
        ));
    }

    /**
     * Update Free User System settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'free_user_system_enabled' => 'required|in:0,1',
            'free_user_can_earn_referral' => 'required|in:0,1',
            'free_user_referral_level' => 'required|integer|min:0|max:10',
            'free_user_step_commission_enabled' => 'required|in:0,1',
            'free_user_step_base_amount' => 'required|numeric|min:0',
            'free_user_step_increment' => 'required|numeric|min:0',
            'free_user_step_max' => 'required|integer|min:1|max:100',
            'free_user_daily_withdraw_limit' => 'required|numeric|min:0',
            'free_user_min_withdraw' => 'required|numeric|min:0',
            'free_user_max_withdraw' => 'required|numeric|min:0',
            'free_user_can_view_ptc' => 'required|in:0,1',
            'free_user_ptc_limit' => 'required|integer|min:0',
            'free_user_ptc_earning' => 'required|numeric|min:0',
            'free_user_can_claim_red_bag' => 'required|in:0,1',
        ]);

        $general = gs();

        // Update basic settings
        $general->free_user_system_enabled = $request->free_user_system_enabled;
        $general->free_user_can_earn_referral = $request->free_user_can_earn_referral;
        $general->free_user_referral_level = $request->free_user_referral_level;

        // Step commission settings
        $general->free_user_step_commission_enabled = $request->free_user_step_commission_enabled;
        $general->free_user_step_base_amount = $request->free_user_step_base_amount;
        $general->free_user_step_increment = $request->free_user_step_increment;
        $general->free_user_step_max = $request->free_user_step_max;

        $general->free_user_daily_withdraw_limit = $request->free_user_daily_withdraw_limit;
        $general->free_user_min_withdraw = $request->free_user_min_withdraw;
        $general->free_user_max_withdraw = $request->free_user_max_withdraw;
        $general->free_user_can_view_ptc = $request->free_user_can_view_ptc;
        $general->free_user_ptc_limit = $request->free_user_ptc_limit;
        $general->free_user_ptc_earning = $request->free_user_ptc_earning;
        $general->free_user_can_claim_red_bag = $request->free_user_can_claim_red_bag;

        $general->save();

        $notify[] = ['success', 'Free User System settings updated successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Get Free Users list
     */
    public function freeUsers()
    {
        $pageTitle = 'Free Users';
        $users = User::where(function($query) {
            $query->whereNull('plan_id')
                ->orWhere('plan_id', 0)
                ->orWhere('expire_date', '<', now());
        })
        ->orderBy('created_at', 'desc')
        ->paginate(getPaginate());

        return view('admin.free_users_list', compact('pageTitle', 'users'));
    }

    /**
     * Get Premium Users list
     */
    public function premiumUsers()
    {
        $pageTitle = 'Premium Users';
        $users = User::whereNotNull('plan_id')
            ->where('plan_id', '>', 0)
            ->where('expire_date', '>=', now())
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('admin.premium_users_list', compact('pageTitle', 'users'));
    }
}
