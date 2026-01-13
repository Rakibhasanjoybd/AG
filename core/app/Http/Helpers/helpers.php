<?php

use App\Lib\GoogleAuthenticator;
use App\Models\Extension;
use App\Models\Frontend;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Models\CommissionLog;
use App\Models\GeneralSetting;
use App\Models\HoldWalletTransaction;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Notify\Notify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

/**
 * Sanitize HTML content to prevent XSS attacks
 * Allows only safe HTML tags and attributes
 *
 * @param string|null $html
 * @return string
 */
function clean($html)
{
    if (empty($html)) {
        return '';
    }
    
    // List of allowed tags
    $allowedTags = '<p><br><b><i><u><strong><em><a><ul><ol><li><img><h1><h2><h3><h4><h5><h6><div><span><table><tr><td><th><thead><tbody>';
    
    // Strip all tags except allowed ones
    $cleaned = strip_tags($html, $allowedTags);
    
    // Remove dangerous attributes (onclick, onerror, javascript:, etc.)
    $cleaned = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
    $cleaned = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $cleaned);
    $cleaned = preg_replace('/javascript\s*:/i', '', $cleaned);
    $cleaned = preg_replace('/vbscript\s*:/i', '', $cleaned);
    $cleaned = preg_replace('/data\s*:/i', '', $cleaned);
    
    return $cleaned;
}

function systemDetails()
{
    $system['name'] = 'ptclab';
    $system['version'] = '3.4';
    $system['build_version'] = '4.2.7';
    return $system;
}

function slug($string)
{
    return Illuminate\Support\Str::slug($string);
}

function verificationCode($length)
{
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1) . '9';
    return random_int($min, $max);
}

function getNumber($length = 8)
{
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function activeTemplate($asset = false)
{
    $general = gs();
    $template = $general->active_template;
    if ($asset) return 'assets/templates/' . $template . '/';
    return 'templates.' . $template . '.';
}

function activeTemplateName()
{
    $general = gs();
    $template = $general->active_template;
    return $template;
}

function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003')
{
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $analytics = Extension::where('act', $key)->where('status', 1)->first();
    return $analytics ? $analytics->generateScript() : '';
}

function getTrx($length = 12)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false)
{
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        }
    }
    return $printAmount;
}


function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$wallet&choe=UTF-8";
}


function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}


function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}


function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}


function getTemplates()
{
    $param['purchasecode'] = env("PURCHASECODE");
    $param['website'] = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");
    $url = 'https://license.viserlab.com/updates/templates/' . systemDetails()['name'];
    $response = CurlRequest::curlPostContent($url, $param);
    if ($response) {
        return $response;
    } else {
        return null;
    }
}


function getPageSections($arr = false)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}


function getImage($image, $size = null)
{
    // Backward compatible behavior:
    // - When the file exists: return its asset URL.
    // - When the file does NOT exist and $size is provided: return placeholder route with that size.
    // Enhancement:
    // - If $size looks like a query string (e.g. '?v=123' or '&v=123'), append it to the asset URL
    //   when the file exists. This fixes stale browser caching for assets that keep the same filename.

    $queryString = '';
    if (is_string($size) && $size !== '' && (str_starts_with($size, '?') || str_starts_with($size, '&'))) {
        $queryString = $size;
    }

    if (file_exists($image) && is_file($image)) {
        return asset($image) . $queryString;
    }

    // If $size was only used as a query string, don't treat it as a placeholder size.
    if ($size && $queryString === '') {
        return route('placeholder.image', $size);
    }

    return asset('assets/images/default.png');
}


function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true)
{
    $general = gs();
    $globalShortCodes = [
        'site_name' => $general->site_name,
        'site_currency' => $general->cur_text,
        'currency_symbol' => $general->cur_sym,
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->userColumn = $user->getForeignKey();
    $notify->send();
}

function getPaginate($paginate = 20)
{
    return $paginate;
}

function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}


function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3) $class = 'side-menu--open';
    elseif ($type == 2) $class = 'sidebar-submenu__open';
    else $class = 'active';

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) return $class;
        }
    } elseif (request()->routeIs($routeName)) {
        if ($param) {
            if (request()->route(@$param[0]) == @$param[1]) return $class;
            else return;
        }
        return $class;
    }
}


function fileUploader($file, $location, $size = null, $old = null, $thumb = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}


function showDateTime($date, $format = 'Y-m-d h:i A')
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}


function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false)
{
    if ($singleQuery) {
        $content = Frontend::where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
    } else {
        $article = Frontend::query();
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}


function gatewayRedirectUrl($type = false)
{
    if ($type) {
        return 'user.deposit.history';
    } else {
        return 'user.deposit';
    }
}

function verifyG2fa($user, $code, $secret = null)
{
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = 1;
        $user->save();
        return true;
    } else {
        return false;
    }
}


function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value)
{
    return request()->fullUrlWithQuery([$key => $value]);
}


function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function levelCommission($referee, $amount, $commissionType, $trx, $sourceType = null, $sourceId = null)
{
    $general = gs();
    if (!$general->$commissionType) {
        return false;
    }

    // Cache schema capability check to avoid repeated queries.
    static $commissionLogsHaveSourceCols = null;
    if ($commissionLogsHaveSourceCols === null) {
        $commissionLogsHaveSourceCols = \Illuminate\Support\Facades\Schema::hasColumn('commission_logs', 'source_type')
            && \Illuminate\Support\Facades\Schema::hasColumn('commission_logs', 'source_id');
    }

    // Commission idempotency key.
    // - source_type/source_id are stored on CommissionLog to guarantee a commission is credited at most once
    //   per credited user per source.
    // - Backward compatible: if caller doesn't provide sourceType/sourceId, fall back to (commissionType, trx).
    $sourceType = $sourceType ?? $commissionType;
    $sourceId   = $sourceId ?? $trx;

    $levels = Referral::where('commission_type', $commissionType)->get();

    $tempReferee = $referee;
    $i = 1;

    while ($i <= $levels->count()) {
        $refererId = $tempReferee->ref_by;
        if (!$refererId) {
            break;
        }

        // Process each commission in its own transaction with lock to prevent race conditions
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($refererId, $referee, $amount, $commissionType, $trx, $levels, $i, $sourceType, $sourceId, $commissionLogsHaveSourceCols) {
                // Lock the referer for update
                $referer = \App\Models\User::lockForUpdate()->find($refererId);
                if (!$referer) {
                    return;
                }

                $plan = $referer->plan;
                if (!$plan || $i > $plan->ref_level) {
                    return;
                }

                $commission = $levels->where('level', $i)->first();
                if (!$commission) {
                    return;
                }

                $com = ($amount * $commission->percent) / 100;

                // 40/60 Split: 40% instant, 60% held for 30 days
                $instantAmount = $com * 0.40;
                $holdAmount = $com * 0.60;

                // Determine hold wallet column based on commission type
                $holdColumn = 'referral_commission_hold';
                if ($commissionType == 'deposit_commission' || $commissionType == 'plan_subscribe_commission') {
                    $holdColumn = 'upgrade_commission_hold';
                } elseif ($commissionType == 'ptc_view_commission') {
                    $holdColumn = 'ptc_commission_hold';
                }

                // Idempotency guard BEFORE side-effects.
                // Prefer DB uniqueness (source_type/source_id) when present.
                if ($commissionLogsHaveSourceCols) {
                    try {
                        CommissionLog::create([
                            'to_id' => $referer->id,
                            'from_id' => $referee->id,
                            'level' => $i,
                            'amount' => $com,
                            'details' => ordinal($i) . ' level referral commission from ' . $referee->username . ' (40% instant: ' . showAmount($instantAmount) . ', 60% held: ' . showAmount($holdAmount) . ')',
                            'type' => $commissionType,
                            'trx' => $trx,
                            'source_type' => $sourceType,
                            'source_id' => $sourceId,
                        ]);
                    } catch (\Illuminate\Database\QueryException $qe) {
                        // MySQL duplicate key: 1062
                        if ((int)($qe->errorInfo[1] ?? 0) === 1062) {
                            return;
                        }
                        throw $qe;
                    }
                } else {
                    // Best-effort fallback when migration hasn't run yet.
                    $alreadyCredited = CommissionLog::where('to_id', $referer->id)
                        ->where('trx', $trx)
                        ->where('type', $commissionType)
                        ->exists();
                    if ($alreadyCredited) {
                        return;
                    }
                }

                // Atomic balance update
                $referer->balance += $instantAmount;
                $referer->$holdColumn += $holdAmount;
                $referer->save();

                // Create hold wallet transaction record
                HoldWalletTransaction::create([
                    'user_id' => $referer->id,
                    'amount' => $com,
                    'instant_amount' => $instantAmount,
                    'hold_amount' => $holdAmount,
                    'commission_type' => str_replace('_commission', '', $commissionType),
                    'from_user_id' => $referee->id,
                    'available_date' => now()->addDays(30)->toDateString(),
                    'trx' => $trx,
                    'source_description' => ordinal($i) . ' level referral commission from ' . $referee->username,
                ]);

                // Transaction for instant amount
                Transaction::create([
                    'user_id' => $referer->id,
                    'amount' => $instantAmount,
                    'post_balance' => $referer->balance,
                    'charge' => 0,
                    'trx_type' => '+',
                    'details' => ordinal($i) . ' level referral commission (40% instant) from ' . $referee->username,
                    'remark' => 'referral_commission',
                    'trx' => $trx,
                ]);
                // If the migration hasn't run, we didn't insert CommissionLog yet.
                if (!$commissionLogsHaveSourceCols) {
                    CommissionLog::create([
                        'to_id' => $referer->id,
                        'from_id' => $referee->id,
                        'level' => $i,
                        'amount' => $com,
                        'details' => ordinal($i) . ' level referral commission from ' . $referee->username . ' (40% instant: ' . showAmount($instantAmount) . ', 60% held: ' . showAmount($holdAmount) . ')',
                        'type' => $commissionType,
                        'trx' => $trx,
                    ]);
                }

                // Create user notification
                UserNotification::create([
                    'user_id' => $referer->id,
                    'title' => 'Commission Received',
                    'message' => 'You received ' . showAmount($com) . ' commission from ' . $referee->username . '. ' . showAmount($instantAmount) . ' added to main balance, ' . showAmount($holdAmount) . ' held for 30 days.',
                    'type' => 'commission',
                ]);

                notify($referer, 'REFERRAL_COMMISSION', [
                    'amount' => showAmount($com),
                    'instant_amount' => showAmount($instantAmount),
                    'hold_amount' => showAmount($holdAmount),
                    'post_balance' => showAmount($referer->balance),
                    'trx' => $trx,
                    'level' => ordinal($i),
                    'type' => ucfirst(str_replace('_', ' ', $commissionType))
                ]);
            }, 3); // 3 retry attempts for deadlocks
        } catch (\Exception $e) {
            // Log error but continue processing other levels
            \Illuminate\Support\Facades\Log::error('Commission processing error: ' . $e->getMessage());
        }

        // Get next in chain (outside transaction)
        $tempReferee = \App\Models\User::find($refererId);
        if (!$tempReferee) {
            break;
        }
        $i++;
    }
}


function ordinal($number)
{
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return $number . 'th';
    } else {
        return $number . $ends[$number % 10];
    }
}

function gs()
{
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    return $general;
}
