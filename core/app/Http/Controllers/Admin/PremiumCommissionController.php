<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumReferralCommission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PremiumCommissionController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Premium Commissions';
        $commissions = $this->commissionData();
        return view('admin.premium_commission.index', compact('pageTitle', 'commissions'));
    }

    public function pending()
    {
        $pageTitle = 'Pending Premium Commissions';
        $commissions = $this->commissionData('pending');
        return view('admin.premium_commission.index', compact('pageTitle', 'commissions'));
    }

    public function approved()
    {
        $pageTitle = 'Approved Premium Commissions';
        $commissions = $this->commissionData('approved');
        return view('admin.premium_commission.index', compact('pageTitle', 'commissions'));
    }

    public function locked()
    {
        $pageTitle = 'Locked Premium Commissions';
        $commissions = $this->commissionData('locked');
        return view('admin.premium_commission.index', compact('pageTitle', 'commissions'));
    }

    public function reversed()
    {
        $pageTitle = 'Reversed Premium Commissions';
        $commissions = $this->commissionData('reversed');
        return view('admin.premium_commission.index', compact('pageTitle', 'commissions'));
    }

    protected function commissionData($status = null)
    {
        $query = PremiumReferralCommission::with(['referrer', 'referredUser', 'plan', 'admin'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        // Search functionality
        if (request()->search) {
            $search = request()->search;
            $query->whereHas('referrer', function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            })->orWhereHas('referredUser', function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        return $query->paginate(getPaginate());
    }

    public function approve($id)
    {
        return DB::transaction(function () use ($id) {
            $commission = PremiumReferralCommission::lockForUpdate()->findOrFail($id);

            if ($commission->status !== 'pending' && $commission->status !== 'locked') {
                $notify[] = ['error', 'Only pending or locked commissions can be approved'];
                return back()->withNotify($notify);
            }

            $referrer = User::lockForUpdate()->find($commission->referrer_id);
            if (!$referrer) {
                $notify[] = ['error', 'Referrer not found'];
                return back()->withNotify($notify);
            }

            // Add commission to referrer balance
            $referrer->balance += $commission->amount;
            $referrer->save();

            // Create transaction
            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $referrer->id;
            $transaction->amount = $commission->amount;
            $transaction->post_balance = $referrer->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->details = 'Premium referral commission approved';
            $transaction->trx = $trx;
            $transaction->remark = 'premium_referral_commission';
            $transaction->save();

            // Update commission status
            $commission->status = 'approved';
            $commission->admin_id = auth()->guard('admin')->id();
            $commission->save();

            $notify[] = ['success', 'Commission approved and credited to referrer'];
            return back()->withNotify($notify);
        });
    }

    public function lock(Request $request, $id)
    {
        $commission = PremiumReferralCommission::findOrFail($id);

        if ($commission->status !== 'pending') {
            $notify[] = ['error', 'Only pending commissions can be locked'];
            return back()->withNotify($notify);
        }

        $commission->status = 'locked';
        $commission->admin_id = auth()->guard('admin')->id();
        $commission->notes = $request->notes ?? 'Locked by admin';
        $commission->save();

        $notify[] = ['success', 'Commission locked successfully'];
        return back()->withNotify($notify);
    }

    public function reverse(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $commission = PremiumReferralCommission::lockForUpdate()->findOrFail($id);

            // If approved, need to deduct from referrer
            if ($commission->status === 'approved') {
                $referrer = User::lockForUpdate()->find($commission->referrer_id);
                if ($referrer) {
                    $referrer->balance -= $commission->amount;
                    $referrer->save();

                    // Create reverse transaction
                    $trx = getTrx();
                    $transaction = new Transaction();
                    $transaction->user_id = $referrer->id;
                    $transaction->amount = $commission->amount;
                    $transaction->post_balance = $referrer->balance;
                    $transaction->charge = 0;
                    $transaction->trx_type = '-';
                    $transaction->details = 'Premium referral commission reversed';
                    $transaction->trx = $trx;
                    $transaction->remark = 'premium_referral_commission_reverse';
                    $transaction->save();
                }
            }

            $commission->status = 'reversed';
            $commission->admin_id = auth()->guard('admin')->id();
            $commission->notes = $request->notes ?? 'Reversed by admin';
            $commission->save();

            $notify[] = ['success', 'Commission reversed successfully'];
            return back()->withNotify($notify);
        });
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,lock,reverse',
            'ids' => 'required|array',
            'ids.*' => 'exists:premium_referral_commissions,id'
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = 0;

        foreach ($ids as $id) {
            try {
                if ($action === 'approve') {
                    $this->approve($id);
                    $count++;
                } elseif ($action === 'lock') {
                    $commission = PremiumReferralCommission::find($id);
                    if ($commission && $commission->status === 'pending') {
                        $commission->status = 'locked';
                        $commission->admin_id = auth()->guard('admin')->id();
                        $commission->save();
                        $count++;
                    }
                } elseif ($action === 'reverse') {
                    $this->reverse($request, $id);
                    $count++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $notify[] = ['success', "$count commissions processed successfully"];
        return back()->withNotify($notify);
    }
}
