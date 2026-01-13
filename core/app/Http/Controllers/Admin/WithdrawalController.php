<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function pending()
    {
        $pageTitle = 'Pending Withdrawals';
        $withdrawals = $this->withdrawalData('pending');
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function approved()
    {
        $pageTitle = 'Approved Withdrawals';
        $withdrawals = $this->withdrawalData('approved');
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function rejected()
    {
        $pageTitle = 'Rejected Withdrawals';
        $withdrawals = $this->withdrawalData('rejected');
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function log()
    {
        $pageTitle = 'Withdrawals Log';
        $withdrawalData = $this->withdrawalData($scope = null, $summery = true);
        $withdrawals = $withdrawalData['data'];
        $summery = $withdrawalData['summery'];
        $successful = $summery['successful'];
        $pending = $summery['pending'];
        $rejected = $summery['rejected'];


        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals','successful','pending','rejected'));
    }

    protected function withdrawalData($scope = null, $summery = false){
        if ($scope) {
            $withdrawals = Withdrawal::$scope();
        }else{
            $withdrawals = Withdrawal::where('status','!=',0);
        }

        $request = request();
        //search
        $search = $request->search;
        $withdrawals = $withdrawals->where('status','!=',0)->where(function ($q) use ($search) {
            $q->where('trx', 'like',"%$search%")
                ->orWhereHas('user', function ($user) use ($search) {
                    $user->where('username', 'like',"%$search%");
            });
        });


        //date search
        if($request->date) {
            $date = explode('-',$request->date);
            $request->merge([
                'start_date'=> trim(@$date[0]),
                'end_date'  => trim(@$date[1])
            ]);
            $request->validate([
                'start_date'    => 'required|date_format:m/d/Y',
                'end_date'      => 'nullable|date_format:m/d/Y'
            ]);
            if($request->end_date) {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $withdrawals   = $withdrawals->whereBetween('created_at', [Carbon::parse($request->start_date), $endDate]);
            }else{
                $withdrawals   = $withdrawals->whereDate('created_at', Carbon::parse($request->start_date));
            }
        }

        //via method
        $methodId = $request->input('method');
        if ($methodId) {
            $withdrawals = $withdrawals->where('method_id', $methodId);
        }
        if (!$summery) {
            return $withdrawals->with(['user','method'])->orderBy('id','desc')->paginate(getPaginate());
        }else{

            $successful = clone $withdrawals;
            $pending = clone $withdrawals;
            $rejected = clone $withdrawals;

            $successfulSummery = $successful->where('status',1)->sum('amount');
            $pendingSummery = $pending->where('status',2)->sum('amount');
            $rejectedSummery = $rejected->where('status',3)->sum('amount');


            return [
                'data'=> $withdrawals->with(['user','method'])->orderBy('id','desc')->paginate(getPaginate()),
                'summery'=>[
                    'successful'=>$successfulSummery,
                    'pending'=>$pendingSummery,
                    'rejected'=>$rejectedSummery,
                ]
            ];
        }
    }

    public function details($id)
    {
        $general = gs();
        $withdrawal = Withdrawal::where('id',$id)->where('status', '!=', 0)->with(['user','method'])->firstOrFail();
        $pageTitle = $withdrawal->user->username.' Withdraw Requested ' . showAmount($withdrawal->amount) . ' '.$general->cur_text;
        $details = $withdrawal->withdraw_information ? json_encode($withdrawal->withdraw_information) : null;

        return view('admin.withdraw.detail', compact('pageTitle', 'withdrawal','details'));
    }

    public function approve(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id',$request->id)->where('status',2)->with('user')->firstOrFail();
        $withdraw->status = 1;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        notify($withdraw->user, 'WITHDRAW_APPROVE', [
            'method_name' => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount' => showAmount($withdraw->final_amount),
            'amount' => showAmount($withdraw->amount),
            'charge' => showAmount($withdraw->charge),
            'rate' => showAmount($withdraw->rate),
            'trx' => $withdraw->trx,
            'admin_details' => $request->details
        ]);

        // Create in-app notification
        UserNotification::create([
            'user_id' => $withdraw->user_id,
            'title' => 'উত্তোলন অনুমোদিত',
            'message' => 'আপনার ৳' . showAmount($withdraw->amount) . ' উত্তোলন অনুরোধ অনুমোদিত হয়েছে এবং প্রক্রিয়াধীন আছে।',
            'type' => 'withdrawal',
        ]);

        $notify[] = ['success', 'Withdrawal approved successfully'];
        return to_route('admin.withdraw.pending')->withNotify($notify);
    }


    public function reject(Request $request)
    {
        $general = gs();
        $request->validate(['id' => 'required|integer']);

        return DB::transaction(function () use ($request, $general) {
            $withdraw = Withdrawal::where('id', $request->id)->where('status', 2)->lockForUpdate()->firstOrFail();
            $user = $withdraw->user()->lockForUpdate()->first();

            $withdraw->status = 3;
            $withdraw->admin_feedback = $request->details;
            $withdraw->save();

            $user->balance += $withdraw->amount;

            if (
                $withdraw->withdraw_information &&
                isset($withdraw->withdraw_information->non_premium_limit_applied) &&
                $withdraw->withdraw_information->non_premium_limit_applied
            ) {
                $user->non_premium_withdraw_used = max(0, ($user->non_premium_withdraw_used ?? 0) - $withdraw->amount);
            }

            $user->save();

            $transaction = new Transaction();
            $transaction->user_id = $withdraw->user_id;
            $transaction->amount = $withdraw->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->remark = 'withdraw_reject';
            $transaction->details = showAmount($withdraw->amount) . ' ' . $general->cur_text . ' Refunded from withdrawal rejection';
            $transaction->trx = $withdraw->trx;
            $transaction->save();




            notify($user, 'WITHDRAW_REJECT', [
                'method_name' => $withdraw->method->name,
                'method_currency' => $withdraw->currency,
                'method_amount' => showAmount($withdraw->final_amount),
                'amount' => showAmount($withdraw->amount),
                'charge' => showAmount($withdraw->charge),
                'rate' => showAmount($withdraw->rate),
                'trx' => $withdraw->trx,
                'post_balance' => showAmount($user->balance),
                'admin_details' => $request->details
            ]);

            // Create in-app notification
            UserNotification::create([
                'user_id' => $user->id,
                'title' => 'উত্তোলন প্রত্যাখ্যাত',
                'message' => 'আপনার ৳' . showAmount($withdraw->amount) . ' উত্তোলন অনুরোধ প্রত্যাখ্যাত হয়েছে এবং টাকা ফেরত দেওয়া হয়েছে। কারণ: ' . ($request->details ?: 'কোনো কারণ উল্লেখ করা হয়নি'),
                'type' => 'withdrawal',
            ]);

            $notify[] = ['success', 'Withdrawal rejected successfully'];
            return to_route('admin.withdraw.pending')->withNotify($notify);
        });
    }

}
