<?php

namespace App\Http\Controllers\User;

use App\Models\Ptc;
use App\Models\PtcView;
use App\Models\PtcReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PtcController extends Controller
{
    // User-facing, reusable messages (Bangla)
    private const MSG_NO_ACTIVE_PLAN = 'আপনার কোনো সক্রিয় সাবস্ক্রিপশন নেই। বিজ্ঞাপন দেখতে আগে একটি প্ল্যান সাবস্ক্রাইব করুন।';
    private const MSG_NOT_ELIGIBLE_LINK = 'দুঃখিত! এই লিংকটি আপনার জন্য প্রযোজ্য নয়।';
    private const MSG_NOT_ELIGIBLE_AD = 'দুঃখিত! এই বিজ্ঞাপনটি আপনার জন্য প্রযোজ্য নয়।';
    private const MSG_OWN_AD_NOT_ALLOWED = 'আপনি নিজের বিজ্ঞাপন দেখতে পারবেন না।';
    private const MSG_DAILY_LIMIT_OVER = 'দুঃখিত! আজকের বিজ্ঞাপন দেখার সীমা শেষ। আপনি আজ আর বিজ্ঞাপন দেখতে পারবেন না।';
    private const MSG_AD_REPEAT_24H = 'একই বিজ্ঞাপন ২৪ ঘণ্টার মধ্যে পুনরায় দেখা যাবে না।';
    private const MSG_DAILY_LIMIT_CROSSED = 'আপনি আজকের বিজ্ঞাপন দেখার সীমা অতিক্রম করেছেন।';
    private const MSG_INSUFFICIENT_BALANCE = 'দুঃখিত! আপনার ব্যালেন্স পর্যাপ্ত নয়।';
    /**
     * Eligible plan IDs for a user.
     *
     * If the user has a plan with a package_number, we allow that tier and lower tiers.
     */
    protected function eligiblePlanIdsForUser(User $user): array
    {
        $userPlanId = (int) ($user->plan_id ?? 0);
        if ($userPlanId <= 0) {
            // Free users (no plan) may still view PTC if Free User System allows it.
            $general = gs();
            $freeSystemEnabled = (int) ($general->free_user_system_enabled ?? 1) === 1;
            $freeCanViewPtc = (int) ($general->free_user_can_view_ptc ?? 1) === 1;

            if (!$freeSystemEnabled || !$freeCanViewPtc) {
                return [];
            }

            // Allow free users to see lowest-tier plan ads (legacy data), plus unrestricted ads.
            $lowestPlanQuery = Plan::where('status', 1);
            // Only order by columns that exist in this installation.
            if (Schema::hasColumn('plans', 'package_number')) {
                $lowestPlanQuery->orderBy('package_number');
            }
            if (Schema::hasColumn('plans', 'sort_order')) {
                $lowestPlanQuery->orderBy('sort_order');
            }
            if (Schema::hasColumn('plans', 'price')) {
                $lowestPlanQuery->orderBy('price');
            }
            $lowestPlanId = $lowestPlanQuery->orderBy('id')->value('id');

            return $lowestPlanId ? [(int) $lowestPlanId] : [];
        }

        $userPlan = Plan::find($userPlanId);
        if (!$userPlan) {
            return [$userPlanId];
        }

        $packageNumber = (int) ($userPlan->package_number ?? 0);
        if ($packageNumber > 0) {
            return Plan::where('status', 1)
                ->where('package_number', '<=', $packageNumber)
                ->pluck('id')
                ->all();
        }

        // Fallbacks for older data where package_number is not configured
        // 1) sort_order (if present)
        if (Schema::hasColumn('plans', 'sort_order') && isset($userPlan->sort_order) && $userPlan->sort_order !== null) {
            return Plan::where('status', 1)
                ->whereNotNull('sort_order')
                ->where('sort_order', '<=', $userPlan->sort_order)
                ->pluck('id')
                ->all();
        }

        // 2) price-based tiers
        if (isset($userPlan->price) && (float) $userPlan->price > 0) {
            return Plan::where('status', 1)
                ->where('price', '<=', $userPlan->price)
                ->pluck('id')
                ->all();
        }

        // 3) same plan only
        return [$userPlanId];
    }

    /**
     * Whether a user can view a given PTC ad based on plan restriction.
     * plan_id NULL/0 means unrestricted.
     */
    protected function userEligibleForAd(User $user, Ptc $ptc): bool
    {
        $requiredPlanId = (int) ($ptc->plan_id ?? 0);
        if ($requiredPlanId <= 0) {
            return true;
        }
        return in_array($requiredPlanId, $this->eligiblePlanIdsForUser($user), true);
    }

    /**
     * Check if PTC is enabled and user is eligible
     * Returns array with 'allowed' bool and 'reason' string
     */
    protected function checkPtcEligibility($user = null): array
    {
        $general = gs();
        $user = $user ?? auth()->user();

        // Check global PTC enable
        if (!($general->ptc_enable_global ?? 1)) {
            return [
                'allowed' => false,
                'reason' => 'PTC বর্তমানে বন্ধ আছে।',
                'reason_en' => 'PTC is currently disabled.',
                'type' => 'global_disabled'
            ];
        }

        // Check if user's PTC income is locked
        if ($user->ptc_income_locked ?? false) {
            return [
                'allowed' => false,
                'reason' => 'আপনার PTC আয় লক করা আছে। অ্যাডমিনের সাথে যোগাযোগ করুন।',
                'reason_en' => 'Your PTC earnings are locked. Contact admin.',
                'type' => 'income_locked'
            ];
        }

        // Check user's PTC unlock level against max
        $userUnlockLevel = $user->ptc_unlock_level ?? 0;
        $maxUnlockLevel = $general->ptc_max_unlock_level ?? 3;

        if ($userUnlockLevel < $maxUnlockLevel) {
            // User needs to unlock more levels to earn from PTC
            // For now we allow viewing but may restrict earnings later
        }

        return [
            'allowed' => true,
            'reason' => '',
            'type' => 'allowed',
            'unlock_level' => $userUnlockLevel,
            'max_unlock_level' => $maxUnlockLevel
        ];
    }

    public function index()
    {
        $pageTitle = "PTC Ads";
        $user = auth()->user();

        // Check PTC eligibility
        $ptcCheck = $this->checkPtcEligibility($user);
        if (!$ptcCheck['allowed']) {
            return view(activeTemplate().'user.ptc.index', [
                'ads' => collect([]),
                'pageTitle' => $pageTitle,
                'ptcDisabled' => true,
                'ptcDisabledReason' => $ptcCheck['reason'],
                'ptcDisabledType' => $ptcCheck['type']
            ]);
        }

        // Filter ads based on user's plan / tier rules
        // - plan_id = 0 or NULL means "All Plans" (no restriction)
        // - if user has a plan, allow ads for their plan and lower tiers (by package_number)
        $userPlanId = (int) ($user->plan_id ?? 0);
        $eligiblePlanIds = $this->eligiblePlanIdsForUser($user);

        $ads = Ptc::where('status', 1)
            ->where('remain', '>', 0)
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) use ($userPlanId, $eligiblePlanIds) {
                // Unrestricted ads (legacy NULL or explicit 0)
                $query->whereNull('plan_id')->orWhere('plan_id', 0);

                // Plan-restricted ads
                if (!empty($eligiblePlanIds)) {
                    $query->orWhereIn('plan_id', $eligiblePlanIds);
                } elseif ($userPlanId > 0) {
                    $query->orWhere('plan_id', $userPlanId);
                }
            })
            ->whereDoesntHave('views', function ($q) use ($user) {
                $q->where('user_id', $user->id)->whereDate('view_date', today());
            })
            ->inRandomOrder()
            ->limit(45)
            ->get();

        return view(activeTemplate().'user.ptc.index', compact('ads', 'pageTitle'));
    }

    public function show($hash)
    {
        $user = auth()->user();
        $general = gs();
        $hasActivePlan = $user->plan_id && $user->expire_date && $user->expire_date >= now();

        // Check PTC eligibility first
        $ptcCheck = $this->checkPtcEligibility($user);
        if (!$ptcCheck['allowed']) {
            $notify[] = ['error', $ptcCheck['reason']];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        }

        // Allow free users if enabled; otherwise require active plan
        if (!$hasActivePlan) {
            if (!((int) ($general->free_user_system_enabled ?? 1) === 1 && (int) ($general->free_user_can_view_ptc ?? 1) === 1)) {
                $notify[] = ['error', self::MSG_NO_ACTIVE_PLAN];
                return back()->withNotify($notify);
            }
        }
        $id = $this->checkEligibleAd($hash,$user);
        if(!$id){
            $notify[] = ['error', self::MSG_NOT_ELIGIBLE_LINK];
            return redirect()->route('user.home')->withNotify($notify);
        }
        $pageTitle = 'Show Advertisement';
        $ptc = Ptc::where('id',$id)->where('remain','>',0)->where('status',1)->firstOrFail();

        // Enforce plan restriction (plan_id NULL/0 means unrestricted)
        if (!$this->userEligibleForAd($user, $ptc)) {
            $notify[] = ['error', self::MSG_NOT_ELIGIBLE_AD];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        }

        if ($user->id == $ptc->user_id) {
            $notify[] = ['error', self::MSG_OWN_AD_NOT_ALLOWED];
            return back()->withNotify($notify);
        }
        $viewads = PtcView::where('user_id',$user->id)->whereDate('view_date',now())->get();

        $dailyLimit = $hasActivePlan ? (int) $user->daily_limit : (int) ($general->free_user_ptc_limit ?? 0);
        if($dailyLimit > 0 && $viewads->count() >= $dailyLimit){
            $notify[] = ['error', self::MSG_DAILY_LIMIT_OVER];
            return back()->withNotify($notify);
        }

        if ($viewads->where('ptc_id',$ptc->id)->first()) {
            $notify[] = ['error', self::MSG_AD_REPEAT_24H];
            return back()->withNotify($notify);
        }

        // Get user's plan for displaying earnings amount
        $userPlan = $hasActivePlan ? Plan::find($user->plan_id) : null;

        return view($this->activeTemplate.'user.ptc.show',compact('ptc','pageTitle','userPlan'));
    }

    public function clicks()
    {
        $pageTitle = 'PTC Clicks';
        $viewads = PtcView::where('user_id', auth()->user()->id)->selectRaw('DATE(view_date) as date')->groupBy('date')->selectRaw('count(id) as total_clicks, sum(amount) as total_earned')->orderBy('date', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate.'user.ptc.earnings',compact('viewads','pageTitle'));
    }

    public function confirm(Request $request,$hash)
    {
        $request->validate([
            'rating'=>'required|integer|min:1|max:5',
            'comment'=>'nullable|string|max:500',
            'security_token'=>'nullable|string',
            'device_fingerprint'=>'nullable|string',
            'watch_time'=>'nullable|integer',
            'tab_switches'=>'nullable|integer',
            'client_timestamp'=>'nullable|integer',
        ]);

        $user = auth()->user();
        $general = gs();
        $hasActivePlan = $user->plan_id && $user->expire_date && $user->expire_date >= now();

        // Check PTC eligibility first
        $ptcCheck = $this->checkPtcEligibility($user);
        if (!$ptcCheck['allowed']) {
            $notify[] = ['error', $ptcCheck['reason']];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        }

        // Allow free users if enabled; otherwise require active plan
        if (!$hasActivePlan) {
            if (!((int) ($general->free_user_system_enabled ?? 1) === 1 && (int) ($general->free_user_can_view_ptc ?? 1) === 1)) {
                $notify[] = ['error', self::MSG_NO_ACTIVE_PLAN];
                return back()->withNotify($notify);
            }
        }
        $id = $this->checkEligibleAd($hash,$user);
        if(!$id){
            $notify[] = ['error', self::MSG_NOT_ELIGIBLE_LINK];
            return redirect()->route('user.home')->withNotify($notify);
        }

        // Server-side fraud validation
        $fraudValidation = $this->validateFraudPrevention($request, $user, $id);
        if ($fraudValidation !== true) {
            $notify[] = ['error', $fraudValidation];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        }

        try {
            return DB::transaction(function () use ($id, $user, $request) {
                $ptc = Ptc::where('id', $id)->where('remain', '>', 0)->where('status', 1)->lockForUpdate()->firstOrFail();
                $user = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

                $general = gs();
                $hasActivePlan = $user->plan_id && $user->expire_date && $user->expire_date >= now();

                // Enforce plan restriction inside transaction (plan_id NULL/0 means unrestricted)
                if (!$this->userEligibleForAd($user, $ptc)) {
                    throw new \Exception(self::MSG_NOT_ELIGIBLE_AD);
                }

                // For paid users: require active plan, for free users: require free-user switch
                if (!$hasActivePlan) {
                    $freeSystemEnabled = (int) ($general->free_user_system_enabled ?? 1) === 1;
                    $freeCanViewPtc = (int) ($general->free_user_can_view_ptc ?? 1) === 1;
                    if (!$freeSystemEnabled || !$freeCanViewPtc) {
                        throw new \Exception(self::MSG_NO_ACTIVE_PLAN);
                    }
                }

                if ($user->id == $ptc->user_id) {
                    throw new \Exception(self::MSG_OWN_AD_NOT_ALLOWED);
                }

                $viewAds = PtcView::where('user_id',$user->id)->whereDate('view_date', now());

                $dailyLimit = $hasActivePlan ? (int) $user->daily_limit : (int) ($general->free_user_ptc_limit ?? 0);
                if($dailyLimit > 0 && $viewAds->count() >= $dailyLimit){
                    throw new \Exception(self::MSG_DAILY_LIMIT_CROSSED);
                }

                if ($viewAds->where('ptc_id',$ptc->id)->first()) {
                    throw new \Exception(self::MSG_AD_REPEAT_24H);
                }

            $ptc->increment('showed');
            $ptc->decrement('remain');
            $ptc->save();

            // Get amount from user's subscription plan (paid) or from free user system settings
            if ($hasActivePlan) {
                $plan = \App\Models\Plan::find($user->plan_id);
                $earnAmount = $plan ? ($plan->ptc_view_amount ?? 0) : 0;
            } else {
                $earnAmount = (float) ($general->free_user_ptc_earning ?? 0);
            }

            $user->balance += $earnAmount;
            $user->save();

            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $earnAmount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->details = 'Earn amount from ads';
            $transaction->trx = $trx;
            $transaction->remark = 'ptc_earn';
            $transaction->save();

            $view               = new PtcView();
            $view->ptc_id       = $ptc->id;
            $view->user_id      = $user->id;
            $view->amount       = $earnAmount;
            $view->view_date    = now();

            // Set fraud tracking fields only if columns exist (migration run)
            if (Schema::hasColumn('ptc_views', 'device_fingerprint')) {
                $view->device_fingerprint = $request->input('device_fingerprint');
                $view->watch_time   = $request->input('watch_time');
                $view->tab_switches = $request->input('tab_switches');
                $view->ip_address   = $request->ip();
            }
            $view->save();

            $review = new PtcReview();
            $review->ptc_id = $ptc->id;
            $review->user_id = $user->id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->save();

            levelCommission($user, $earnAmount, 'ptc_view_commission', $trx);

            $adminNotification = new AdminNotification();
            $adminNotification->user_id = $user->id;
            $adminNotification->title = 'PTC Ad Completed';
            $adminNotification->click_url = urlPath('admin.report.ptcview', $ptc->id);
            $adminNotification->save();

            notify($user, 'PTC_COMPLETE', [
                'amount' => showAmount($earnAmount),
                'post_balance' => showAmount($user->balance),
                'trx' => $trx,
            ]);

            $notify[] = ['success','কাজ সফলভাবে জমা হয়েছে! টাকা আপনার একাউন্টে যোগ করা হয়েছে।'];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        });
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return redirect()->route('user.ptc.index')->withNotify($notify);
        }
    }

    protected function checkEligibleAd($hash,$user)
    {
        $decrypted      = decrypt($hash);
        $decryptedData  = explode('|', $decrypted);
        $id             = $decryptedData[0];

        if($decryptedData[1] != $user->id){
            return false;
        }
        return $id;
    }

    /**
     * Validate fraud prevention data from client
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @param int $ptcId
     * @return bool|string Returns true if valid, error message if invalid
     */
    protected function validateFraudPrevention(Request $request, User $user, $ptcId)
    {
        $ptc = Ptc::find($ptcId);
        if (!$ptc) {
            return 'বিজ্ঞাপন খুঁজে পাওয়া যায়নি';
        }

        // Validate security token (hourly rotating token with server secret)
        $secret = config('app.key', env('APP_KEY', 'default-secret'));
        $expectedToken = hash_hmac('sha256', $user->id . '|' . $ptcId . '|' . date('Y-m-d H'), $secret);
        $expectedTokenPrevHour = hash_hmac('sha256', $user->id . '|' . $ptcId . '|' . date('Y-m-d H', strtotime('-1 hour')), $secret);

        $clientToken = $request->input('security_token');
        if ($clientToken && $clientToken !== $expectedToken && $clientToken !== $expectedTokenPrevHour) {
            // Log suspicious activity
            Log::warning('PTC Fraud: Invalid security token', [
                'user_id' => $user->id,
                'ptc_id' => $ptcId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return 'নিরাপত্তা যাচাই ব্যর্থ হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।';
        }

        // Validate watch time (must be at least 80% of required duration)
        $watchTime = (int) $request->input('watch_time', 0);
        $requiredDuration = $ptc->duration;
        $minimumWatchTime = floor($requiredDuration * 0.8); // Allow 20% tolerance

        if ($watchTime > 0 && $watchTime < $minimumWatchTime) {
            Log::warning('PTC Fraud: Insufficient watch time', [
                'user_id' => $user->id,
                'ptc_id' => $ptcId,
                'watch_time' => $watchTime,
                'required' => $requiredDuration,
                'ip' => $request->ip()
            ]);
            return 'বিজ্ঞাপন সম্পূর্ণ দেখা হয়নি। অনুগ্রহ করে পুরোটা দেখুন।';
        }

        // Check for excessive tab switches (more than 5 is suspicious)
        $tabSwitches = (int) $request->input('tab_switches', 0);
        if ($tabSwitches > 5) {
            Log::warning('PTC Fraud: Excessive tab switches', [
                'user_id' => $user->id,
                'ptc_id' => $ptcId,
                'tab_switches' => $tabSwitches,
                'ip' => $request->ip()
            ]);
            // Don't block, but log for analysis
        }

        // Validate client timestamp (not too far in past or future)
        $clientTimestamp = (int) $request->input('client_timestamp', 0);
        if ($clientTimestamp > 0) {
            $serverTimestamp = round(microtime(true) * 1000);
            $timeDiff = abs($serverTimestamp - $clientTimestamp);

            // Allow 5 minutes tolerance (300000 ms)
            if ($timeDiff > 300000) {
                Log::warning('PTC Fraud: Timestamp mismatch', [
                    'user_id' => $user->id,
                    'ptc_id' => $ptcId,
                    'client_ts' => $clientTimestamp,
                    'server_ts' => $serverTimestamp,
                    'diff_ms' => $timeDiff,
                    'ip' => $request->ip()
                ]);
                // Don't block for clock drift, just log
            }
        }

        // Store device fingerprint for future analysis (only if column exists)
        $fingerprint = $request->input('device_fingerprint');
        if ($fingerprint && Schema::hasColumn('ptc_views', 'device_fingerprint')) {
            // Check if same fingerprint used by different user recently
            $recentViews = PtcView::where('device_fingerprint', $fingerprint)
                ->where('user_id', '!=', $user->id)
                ->where('view_date', '>=', now()->subHours(24))
                ->count();

            if ($recentViews > 0) {
                Log::warning('PTC Fraud: Same device fingerprint from multiple users', [
                    'user_id' => $user->id,
                    'ptc_id' => $ptcId,
                    'fingerprint' => $fingerprint,
                    'other_views' => $recentViews,
                    'ip' => $request->ip()
                ]);
            }
        }

        return true;
    }

    public function ads()
    {
        $this->userPostEnabled();
        $pageTitle = 'My Ads';
        $ads = Ptc::where('user_id',auth()->id())->orderBy('id','desc')->paginate(getPaginate());
        return view($this->activeTemplate.'user.ptc.ads',compact('ads','pageTitle'));
    }

    public function create()
    {
        $this->userPostEnabled();
        $pageTitle = 'Create Ads';
        return view($this->activeTemplate.'user.ptc.create',compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $this->userPostEnabled();
        $this->validation($request,[
            'website_link' => 'nullable|url|required_without_all:banner_image,script,youtube',
            'banner_image' => 'nullable|mimes:jpeg,jpg,png,gif|required_without_all:website_link,script,youtube',
            'script' => 'nullable|required_without_all:website_link,banner_image,youtube',
            'youtube' => 'nullable|url|required_without_all:website_link,banner_image,script',
            'max_show' => 'required|integer|min:1',
        ]);

        $ptc = new Ptc();
        return $this->submit($request,$ptc);
    }

    public function edit($id)
    {
        $this->userPostEnabled();
        $pageTitle = 'Edit Ads';
        $ptc = Ptc::where('user_id',auth()->id())->where('status','!=',3)->findOrFail($id);
        return view($this->activeTemplate.'user.ptc.edit',compact('pageTitle','ptc'));
    }

    public function update(Request $request,$id)
    {
        $this->userPostEnabled();
        $this->validation($request);
        $ptc = Ptc::where('user_id',auth()->id())->where('status','!=',3)->findOrFail($id);
        return $this->submit($request,$ptc,1);
    }

    public function submit($request,$ptc,$isUpdate = 0)
    {
        $this->userPostEnabled();
        $user = auth()->user();
        $message = 'বিজ্ঞাপনটি সফলভাবে আপডেট হয়েছে।';
        $general = gs();
        if($isUpdate == 0){
            $message = 'বিজ্ঞাপনটি সফলভাবে যোগ করা হয়েছে।';
            if ($request->ads_type == 1) {
                $price = @$general->ads_setting->ad_price->url ?? 0;
                $userAmo = @$general->ads_setting->amount_for_user->url ?? 0;
            } elseif ($request->ads_type == 2) {
                $price = @$general->ads_setting->ad_price->image ?? 0;
                $userAmo = @$general->ads_setting->amount_for_user->image ?? 0;
            } elseif($request->ads_type == 3) {
                $price = @$general->ads_setting->ad_price->script ?? 0;
                $userAmo = @$general->ads_setting->amount_for_user->script ?? 0;
            } else {
                $price = @$general->ads_setting->ad_price->youtube ?? 0;
                $userAmo = @$general->ads_setting->amount_for_user->youtube ?? 0;
            }
            $totalPrice = $price * $request->max_show;

            try {
                DB::transaction(function () use ($user, $totalPrice, $request, $ptc, $userAmo) {
                    $lockedUser = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();
                    if (!$lockedUser) {
                        throw new \Exception('User not found');
                    }

                    if ($lockedUser->balance < $totalPrice) {
                        throw new \Exception('INSUFFICIENT_BALANCE');
                    }

                    $lockedUser->balance -= $totalPrice;
                    $lockedUser->save();

                    $trx = getTrx();
                    $transaction = new Transaction();
                    $transaction->user_id = $lockedUser->id;
                    $transaction->amount = $totalPrice;
                    $transaction->post_balance = $lockedUser->balance;
                    $transaction->charge = 0;
                    $transaction->trx_type = '-';
                    $transaction->details = 'PTC ad create';
                    $transaction->trx = $trx;
                    $transaction->remark = 'ad_create';
                    $transaction->save();

                    $ptc->user_id = $lockedUser->id;
                    $ptc->amount = $userAmo;
                    // User-created ads are viewable by all plans unless you add a plan selector in UI
                    $ptc->plan_id = 0;
                    $ptc->max_show = $request->max_show;
                    $ptc->remain = $request->max_show;
                    $user->balance = $lockedUser->balance;
                });
            } catch (\Exception $e) {
                if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                    $notify[] = ['error', self::MSG_INSUFFICIENT_BALANCE];
                    return back()->withNotify($notify);
                }
                throw $e;
            }
        }

        $ptc->title = $request->title;
        $ptc->duration = $request->duration;
        $ptc->ads_type = $request->ads_type;
        $ptc->status = $general->ad_auto_approve ? 1 : 2;


        if($request->ads_type == 1){
            $ptc->ads_body = $request->website_link;
        }elseif($request->ads_type == 2){
            if ($request->hasFile('banner_image')) {
                if ($isUpdate == 1) {
                    $old = $ptc->ads_body;
                    fileManager()->removeFile(getFilePath('ptc').'/'.$old);
                }
                $directory = date("Y")."/".date("m")."/".date("d");
                $path = getFilePath('ptc').'/'.$directory;
                $filename = $directory.'/'.fileUploader($request->banner_image, $path);
                $ptc->ads_body = $filename;
            }
        }elseif($request->ads_type == 3){
            $ptc->ads_body = $request->script;
        }else{
            $ptc->ads_body = $request->youtube;
        }

        $ptc->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }


    public function validation($request,$rules = [])
    {
        $globalRules = [
            'title' => 'required',
            'duration' => 'required|integer|min:1',
            'ads_type' => 'required|integer|in:1,2,3,4',
        ];
        $rules = array_merge($globalRules,$rules);
        $request->validate($rules);
    }

    public function status($id)
    {
        $this->userPostEnabled();
        $ptc = Ptc::where('user_id',auth()->id())->whereIn('status',[1,0])->findOrFail($id);
        if ($ptc->status == 1) {
            $ptc->status = 0;
            $notify[] = ['success','বিজ্ঞাপনটি সফলভাবে বন্ধ করা হয়েছে।'];
        }else{
            $ptc->status = 1;
            $notify[] = ['success','বিজ্ঞাপনটি সফলভাবে চালু করা হয়েছে।'];
        }
        $ptc->save();
        return back()->withNotify($notify);
    }

    private function userPostEnabled()
    {
        $general = gs();
        if (!$general->user_ads_post) {
            abort(404);
        }
    }
}
