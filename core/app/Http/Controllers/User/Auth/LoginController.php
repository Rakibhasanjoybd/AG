<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected $username;

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->username = 'mobile'; // Changed to mobile login
        $this->activeTemplate = activeTemplate();
    }

    public function showLoginForm()
    {
        $pageTitle = "Login";
        $info = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $detectedCountry = @implode(',', $info['code']); // Auto-detect country from IP
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $general = gs();
        $allowedCountryCodes = [];
        $forcedCountryCode = '';

        if (@$general->country_restriction) {
            $allowedCountryCodes = is_array($general->allowed_countries) ? array_values($general->allowed_countries) : [];
            $forcedCountryCode = (string)@$general->forced_country_code;

            if ($forcedCountryCode) {
                $allowedCountryCodes = [$forcedCountryCode];
                $detectedCountry = $forcedCountryCode;
            }

            if ($allowedCountryCodes) {
                $countries = collect($countries)->only($allowedCountryCodes)->all();

                if ($detectedCountry && !in_array($detectedCountry, $allowedCountryCodes)) {
                    $detectedCountry = (string)($allowedCountryCodes[0] ?? '');
                }
            }
        }

        return view($this->activeTemplate . 'user.auth.login', compact('pageTitle', 'mobileCode', 'countries', 'detectedCountry', 'allowedCountryCodes', 'forcedCountryCode'));
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Regenerate CSRF token to prevent token mismatch errors
        $request->session()->regenerateToken();

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
        if (!isset($countryData[$request->country_code])) {
            $notify[] = ['error', 'Invalid country selection'];
            return back()->withNotify($notify)->withInput();
        }

        $expectedDialCode = $countryData[$request->country_code]['dial_code'] ?? null;
        if ($expectedDialCode && (string)$request->mobile_code !== (string)$expectedDialCode) {
            $notify[] = ['error', 'Invalid mobile code for selected country'];
            return back()->withNotify($notify)->withInput();
        }

        $expectedCountry = $countryData[$request->country_code]['country'] ?? null;
        if ($expectedCountry && (string)$request->country !== (string)$expectedCountry) {
            $notify[] = ['error', 'Invalid country for selected country code'];
            return back()->withNotify($notify)->withInput();
        }

        $general = gs();
        if (@$general->country_restriction) {
            $allowed = is_array($general->allowed_countries) ? array_values($general->allowed_countries) : [];
            $forced = (string)@$general->forced_country_code;
            if ($forced) {
                $allowed = [$forced];
            }

            if ($allowed && !in_array($request->country_code, $allowed)) {
                $notify[] = ['error', 'Country is not allowed'];
                return back()->withNotify($notify)->withInput();
            }
        }

        // Build full mobile number with country code
        $fullMobile = $request->mobile_code . $request->mobile;

        // Find user by mobile number
        $user = User::where('mobile', $fullMobile)->first();

        if (!$user) {
            $notify[] = ['error', 'প্রিয় সহযোগী, আপনি হয়তো কিছু ভুল লিখছেন / VPN For: আপনি VPN টি ডিসকানেক্ট করুন'];
            return back()->withNotify($notify)->withInput();
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // Attempt login with mobile number
        if (auth()->attempt(['mobile' => $fullMobile, 'password' => $request->password], $request->boolean('remember'))) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $notify[] = ['error', 'প্রিয় সহযোগী, আপনি হয়তো কিছু ভুল লিখছেন / VPN For: আপনি VPN টি ডিসকানেক্ট করুন'];
        return back()->withNotify($notify)->withInput();
    }

    public function username()
    {
        return 'mobile';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'mobile_code' => 'required|string',
            'country' => 'required|string',
            'country_code' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();

        request()->session()->invalidate();

        $notify[] = ['success', 'You have been logged out.'];
        return to_route('user.login')->withNotify($notify);
    }





    public function authenticated(Request $request, $user)
    {
        $user->tv = $user->ts == 1 ? 0 : 1;
        $user->save();
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        }else{
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  @implode(',',$info['long']);
            $userLogin->latitude =  @implode(',',$info['lat']);
            $userLogin->city =  @implode(',',$info['city']);
            $userLogin->country_code = @implode(',',$info['code']);
            $userLogin->country =  @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();

        return to_route('user.home');
    }


}
