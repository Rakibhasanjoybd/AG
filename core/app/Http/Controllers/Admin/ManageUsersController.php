<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ManageUsersController extends Controller
{

    public function allUsers()
    {
        $pageTitle = 'All Users';
        $users = $this->userData();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function activeUsers()
    {
        $pageTitle = 'Active Users';
        $users = $this->userData('active');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Users';
        $users = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified Users';
        $users = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycUnverifiedUsers()
    {
        $pageTitle = 'KYC Unverified Users';
        $users = $this->userData('kycUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycPendingUsers()
    {
        $pageTitle = 'KYC Unverified Users';
        $users = $this->userData('kycPending');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified Users';
        $users = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileUnverifiedUsers()
    {
        $pageTitle = 'Mobile Unverified Users';
        $users = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileVerifiedUsers()
    {
        $pageTitle = 'Mobile Verified Users';
        $users = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function usersWithBalance()
    {
        $pageTitle = 'Users with Balance';
        $users = $this->userData('withBalance');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    protected function userData($scope = null){
        if ($scope) {
            $users = User::$scope();
        }else{
            $users = User::query();
        }

        //search
        $request = request();
        if ($request->search) {
            $search = $request->search;
            $users  = $users->where(function ($user) use ($search) {
                            $user->where('username', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                      });
        }
        return $users->orderBy('id','desc')->paginate(getPaginate());
    }


    public function detail($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Detail - '.$user->username;

        $totalDeposit = Deposit::where('user_id',$user->id)->where('status',1)->sum('amount');
        $totalWithdrawals = Withdrawal::where('user_id',$user->id)->where('status',1)->sum('amount');
        $totalTransaction = Transaction::where('user_id',$user->id)->count();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user','totalDeposit','totalWithdrawals','totalTransaction','countries'));
    }


    public function kycDetails($id)
    {
        $pageTitle = 'KYC Details';
        $user = User::findOrFail($id);
        return view('admin.users.kyc_detail', compact('pageTitle','user'));
    }

    public function kycApprove($id)
    {
        $user = User::findOrFail($id);
        $user->kv = 1;
        $user->save();

        notify($user,'KYC_APPROVE',[]);

        // Create in-app notification
        \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'title' => 'KYC অনুমোদিত',
            'message' => 'আপনার KYC যাচাইকরণ সফলভাবে অনুমোদিত হয়েছে। এখন আপনি সকল সুবিধা ব্যবহার করতে পারবেন।',
            'type' => 'kyc',
        ]);

        $notify[] = ['success','KYC approved successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function kycReject($id)
    {
        $user = User::findOrFail($id);
        if ($user->kyc_data) {
            foreach ($user->kyc_data as $kycData) {
                if ($kycData->type == 'file') {
                    fileManager()->removeFile(getFilePath('verify').'/'.$kycData->value);
                }
            }
        }
        $user->kv = 0;
        $user->kyc_data = null;
        $user->save();

        notify($user,'KYC_REJECT',[]);

        // Create in-app notification
        \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'title' => 'KYC প্রত্যাখ্যাত',
            'message' => 'আপনার KYC যাচাইকরণ প্রত্যাখ্যাত হয়েছে। অনুগ্রহ করে সঠিক তথ্য দিয়ে পুনরায় আবেদন করুন।',
            'type' => 'kyc',
        ]);

        $notify[] = ['success','KYC rejected successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray   = (array)$countryData;
        $countries      = implode(',', array_keys($countryArray));

        $countryCode    = $request->country;
        $country        = $countryData->$countryCode->country;
        $dialCode       = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname' => 'required|string|max:40',
            'email' => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile' => 'required|string|max:40|unique:users,mobile,' . $user->id,
            'country' => 'required|in:'.$countries,
        ]);
        $user->mobile = $dialCode.$request->mobile;
        $user->country_code = $countryCode;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->address = [
                            'address' => $request->address,
                            'city' => $request->city,
                            'state' => $request->state,
                            'zip' => $request->zip,
                            'country' => @$country,
                        ];
        $user->ev = $request->ev ? 1 : 0;
        $user->sv = $request->sv ? 1 : 0;
        $user->ts = $request->ts ? 1 : 0;
        if (!$request->kv) {
            $user->kv = 0;
            $user->kyc_data = null;
        }else{
            $user->kv = 1;
        }
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act' => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $amount = $request->amount;
        $general = gs();
        $trx = getTrx();

        return DB::transaction(function () use ($request, $id, $amount, $general, $trx) {
            $notify = [];
            $user = User::where('id', $id)->lockForUpdate()->firstOrFail();

            $transaction = new Transaction();

            if ($request->act == 'add') {
                $user->balance += $amount;

                $transaction->trx_type = '+';
                $transaction->remark = 'balance_add';

                $notifyTemplate = 'BAL_ADD';

                $notify[] = ['success', $general->cur_sym . $amount . ' added successfully'];
            } else {
                if ($amount > $user->balance) {
                    $notify[] = ['error', $user->username . ' doesn\'t have sufficient balance.'];
                    return back()->withNotify($notify);
                }

                $user->balance -= $amount;

                $transaction->trx_type = '-';
                $transaction->remark = 'balance_subtract';

                $notifyTemplate = 'BAL_SUB';
                $notify[] = ['success', $general->cur_sym . $amount . ' subtracted successfully'];
            }

            $user->save();

            $transaction->user_id = $user->id;
            $transaction->amount = $amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx = $trx;
            $transaction->details = $request->remark;
            $transaction->save();

            notify($user, $notifyTemplate, [
                'trx' => $trx,
                'amount' => showAmount($amount),
                'post_balance' => showAmount($user->balance),
                'remark' => $request->remark,
            ]);

            // Create in-app notification
            $notificationTitle = $request->act == 'add' ? 'ব্যালেন্স যোগ করা হয়েছে' : 'ব্যালেন্স বিয়োগ করা হয়েছে';
            $notificationMessage = $request->act == 'add'
                ? 'আপনার অ্যাকাউন্টে ৳' . showAmount($amount) . ' যোগ করা হয়েছে। কারণ: ' . $request->remark
                : 'আপনার অ্যাকাউন্ট থেকে ৳' . showAmount($amount) . ' বিয়োগ করা হয়েছে। কারণ: ' . $request->remark;

            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'title' => $notificationTitle,
                'message' => $notificationMessage,
                'type' => 'balance',
            ]);

            return back()->withNotify($notify);
        });
    }

    public function login($id){
        $admin = auth('admin')->user();
        $targetUser = User::findOrFail($id);

        Log::warning('Admin impersonation', [
            'admin_id' => $admin ? $admin->id : null,
            'admin_username' => $admin ? $admin->username : null,
            'target_user_id' => $targetUser->id,
            'target_username' => $targetUser->username,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        AdminNotification::create([
            'user_id' => $targetUser->id,
            'title' => 'Admin ' . ($admin ? $admin->username : 'unknown') . ' logged in as ' . $targetUser->username,
            'click_url' => urlPath('admin.users.detail', $targetUser->id),
        ]);

        session()->put('impersonated_by_admin_id', $admin ? $admin->id : null);
        session()->put('impersonated_by_admin_username', $admin ? $admin->username : null);
        session()->put('impersonated_at', now()->toDateTimeString());

        Auth::loginUsingId($targetUser->id);
        return to_route('user.home');
    }

    public function status(Request $request,$id)
    {
        $user = User::findOrFail($id);
        if ($user->status == 1) {
            $request->validate([
                'reason'=>'required|string|max:255'
            ]);
            $user->status = 0;
            $user->ban_reason = $request->reason;
            $notify[] = ['success','User banned successfully'];
        }else{
            $user->status = 1;
            $user->ban_reason = null;
            $notify[] = ['success','User unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);

    }


    public function showNotificationSingleForm($id)
    {
        $user = User::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.users.detail',$user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        notify($user,'DEFAULT',[
            'subject'=>$request->subject,
            'message'=>$request->message,
        ]);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $users = User::where('ev',1)->where('sv',1)->where('status',1)->count();
        $pageTitle = 'Notification to Verified Users';
        return view('admin.users.notification_all', compact('pageTitle','users'));
    }

    public function sendNotificationAll(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'message' => 'required',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $user = User::where('status', 1)->where('ev',1)->where('sv',1)->skip($request->skip)->first();

        notify($user,'DEFAULT',[
            'subject'=>$request->subject,
            'message'=>$request->message,
        ]);

        return response()->json([
            'success'=>'message sent',
            'total_sent'=>$request->skip + 1,
        ]);
    }

    public function notificationLog($id){
        $user = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to '.$user->username;
        $logs = NotificationLog::where('user_id',$id)->with('user')->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle','logs','user'));
    }

    /**
     * Reset user's wallet withdrawal PIN
     */
    public function resetWalletPin(Request $request, $id)
    {
        $request->validate([
            'new_pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ], [
            'new_pin.required' => 'নতুন পিন প্রয়োজন',
            'new_pin.min' => 'পিন কমপক্ষে ৪ ডিজিট হতে হবে',
            'new_pin.max' => 'পিন সর্বোচ্চ ৬ ডিজিট হতে পারবে',
            'new_pin.regex' => 'পিনে শুধুমাত্র সংখ্যা থাকতে হবে',
        ]);

        $user = User::findOrFail($id);
        
        // Hash the new PIN before storing
        $user->withdrawal_pin = \Illuminate\Support\Facades\Hash::make($request->new_pin);
        $user->save();

        // Log this action for security
        $admin = auth('admin')->user();
        Log::info('Admin reset user wallet PIN', [
            'admin_id' => $admin ? $admin->id : null,
            'admin_username' => $admin ? $admin->username : null,
            'target_user_id' => $user->id,
            'target_username' => $user->username,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Create admin notification
        AdminNotification::create([
            'user_id' => $user->id,
            'title' => 'Wallet PIN reset for ' . $user->username . ' by Admin ' . ($admin ? $admin->username : 'unknown'),
            'click_url' => urlPath('admin.users.detail', $user->id),
        ]);

        // Create in-app notification for user
        \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'title' => 'উত্তোলন পিন রিসেট',
            'message' => 'আপনার উত্তোলন পিন অ্যাডমিন দ্বারা রিসেট করা হয়েছে। নতুন পিন ব্যবহার করুন।',
            'type' => 'security',
        ]);

        $notify[] = ['success', 'User wallet PIN has been reset successfully'];
        return back()->withNotify($notify);
    }

}
