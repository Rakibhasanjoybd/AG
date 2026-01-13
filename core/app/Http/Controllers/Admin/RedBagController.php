<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedBag;
use App\Models\RedBagClaim;
use App\Models\RedBagDevice;
use Illuminate\Http\Request;

class RedBagController extends Controller
{
    public function index()
    {
        $pageTitle = 'Red Bag Management';
        $redBags = RedBag::latest()->paginate(getPaginate());
        return view('admin.red_bag.index', compact('pageTitle', 'redBags'));
    }

    public function create()
    {
        $pageTitle = 'Create Red Bag';
        return view('admin.red_bag.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gte:min_amount',
            'daily_limit' => 'required|integer|min:1',
            'new_user_bonus_count' => 'required|integer|min:0',
            'new_user_days' => 'required|integer|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'win_probability' => 'required|numeric|min:0|max:100',
            'total_daily_budget' => 'required|numeric|min:0',
            'winning_message' => 'nullable|string',
            'losing_message' => 'nullable|string',
        ]);

        $redBag = new RedBag();
        $redBag->name = $request->name;
        $redBag->min_amount = $request->min_amount;
        $redBag->max_amount = $request->max_amount;
        $redBag->daily_limit = $request->daily_limit;
        $redBag->new_user_bonus_count = $request->new_user_bonus_count;
        $redBag->new_user_days = $request->new_user_days;
        $redBag->start_time = $request->start_time;
        $redBag->end_time = $request->end_time;
        $redBag->win_probability = $request->win_probability;
        $redBag->total_daily_budget = $request->total_daily_budget;
        $redBag->status = $request->status ? 1 : 0;
        $redBag->require_referral = $request->require_referral ? 1 : 0;
        $redBag->min_referrals = $request->min_referrals ?? 0;
        $redBag->winning_message = $request->winning_message ?? 'আহা! আপনি {amount} টাকা পেয়েছেন!';
        $redBag->losing_message = $request->losing_message ?? 'তুমি আগামীকাল ভালো কিছু পাবে, তোমার জন্য শুভকামনা।';
        $redBag->save();

        $notify[] = ['success', 'Red Bag created successfully'];
        return back()->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Red Bag';
        $redBag = RedBag::findOrFail($id);
        return view('admin.red_bag.edit', compact('pageTitle', 'redBag'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gte:min_amount',
            'daily_limit' => 'required|integer|min:1',
            'new_user_bonus_count' => 'required|integer|min:0',
            'new_user_days' => 'required|integer|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'win_probability' => 'required|numeric|min:0|max:100',
            'total_daily_budget' => 'required|numeric|min:0',
        ]);

        $redBag = RedBag::findOrFail($id);
        $redBag->name = $request->name;
        $redBag->min_amount = $request->min_amount;
        $redBag->max_amount = $request->max_amount;
        $redBag->daily_limit = $request->daily_limit;
        $redBag->new_user_bonus_count = $request->new_user_bonus_count;
        $redBag->new_user_days = $request->new_user_days;
        $redBag->start_time = $request->start_time;
        $redBag->end_time = $request->end_time;
        $redBag->win_probability = $request->win_probability;
        $redBag->total_daily_budget = $request->total_daily_budget;
        $redBag->status = $request->status ? 1 : 0;
        $redBag->require_referral = $request->require_referral ? 1 : 0;
        $redBag->min_referrals = $request->min_referrals ?? 0;
        $redBag->winning_message = $request->winning_message;
        $redBag->losing_message = $request->losing_message;
        $redBag->save();

        $notify[] = ['success', 'Red Bag updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $redBag = RedBag::findOrFail($id);
        $redBag->status = !$redBag->status;
        $redBag->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $redBag = RedBag::findOrFail($id);
        $redBag->claims()->delete();
        $redBag->delete();

        $notify[] = ['success', 'Red Bag deleted successfully'];
        return back()->withNotify($notify);
    }

    public function claims(Request $request)
    {
        $pageTitle = 'Red Bag Claims';

        $claims = RedBagClaim::with(['user', 'redBag'])
            ->when($request->search, function($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('username', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->fraud_only, function($query) {
                $query->where('is_fraudulent', true);
            })
            ->latest()
            ->paginate(getPaginate());

        return view('admin.red_bag.claims', compact('pageTitle', 'claims'));
    }

    public function devices(Request $request)
    {
        $pageTitle = 'Red Bag Devices';

        $devices = RedBagDevice::with('firstUser')
            ->when($request->blocked_only, function($query) {
                $query->where('is_blocked', true);
            })
            ->latest()
            ->paginate(getPaginate());

        return view('admin.red_bag.devices', compact('pageTitle', 'devices'));
    }

    public function blockDevice($id)
    {
        $device = RedBagDevice::findOrFail($id);
        $device->is_blocked = !$device->is_blocked;
        $device->block_reason = $device->is_blocked ? 'Blocked by admin' : null;
        $device->save();

        $notify[] = ['success', 'Device status updated'];
        return back()->withNotify($notify);
    }

    public function markFraud($id)
    {
        $claim = RedBagClaim::findOrFail($id);
        $claim->is_fraudulent = !$claim->is_fraudulent;
        $claim->fraud_reason = $claim->is_fraudulent ? 'Marked by admin' : null;
        $claim->save();

        // If marked as fraud, reverse the balance
        if ($claim->is_fraudulent && $claim->is_winner && $claim->amount > 0) {
            $user = $claim->user;
            if ($user && $user->balance >= $claim->amount) {
                $user->balance -= $claim->amount;
                $user->save();

                $notify[] = ['warning', 'Amount reversed from user balance'];
            }
        }

        $notify[] = ['success', 'Claim fraud status updated'];
        return back()->withNotify($notify);
    }

    public function statistics()
    {
        $pageTitle = 'Red Bag Statistics';

        $stats = [
            'total_claims' => RedBagClaim::count(),
            'total_winners' => RedBagClaim::where('is_winner', true)->count(),
            'total_payout' => RedBagClaim::where('is_winner', true)->sum('amount'),
            'today_claims' => RedBagClaim::whereDate('created_at', today())->count(),
            'today_payout' => RedBagClaim::where('is_winner', true)->whereDate('created_at', today())->sum('amount'),
            'fraud_claims' => RedBagClaim::where('is_fraudulent', true)->count(),
            'blocked_devices' => RedBagDevice::where('is_blocked', true)->count(),
            'unique_devices' => RedBagDevice::count(),
        ];

        $dailyStats = RedBagClaim::selectRaw('DATE(created_at) as date, COUNT(*) as claims, SUM(CASE WHEN is_winner = 1 THEN amount ELSE 0 END) as payout')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return view('admin.red_bag.statistics', compact('pageTitle', 'stats', 'dailyStats'));
    }
}
