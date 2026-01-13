<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Models\Plan;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Image;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'General Setting';
        $timezones = json_decode(file_get_contents(resource_path('views/admin/partials/timezone.json')));
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $plans = Plan::where('status', 1)->get();
        return view('admin.setting.general', compact('pageTitle', 'timezones', 'countries', 'plans'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:40',
            'cur_text' => 'required|string|max:40',
            'cur_sym' => 'required|string|max:40',
            'base_color' => ['nullable', 'regex:/^[a-f0-9]{6}$/i'],
            'secondary_color' => ['nullable', 'regex:/^[a-f0-9]{6}$/i'],
            'wallet_images.*' => 'image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'wallet_image_effect' => 'nullable|string|max:50',
            'wallet_header_slideshow' => 'nullable',
            'registration_bonus' => 'required|gte:0',
            'default_plan' => 'required',
            'timezone' => 'required',
            'country_restriction' => 'nullable',
            'allowed_countries' => 'nullable',
            'forced_country_code' => 'nullable|string|max:10',
        ]);

        $defaultPlan = $request->default_plan;
        if ($defaultPlan > 0) {
            $plan = Plan::where('status', 1)->findOrFail($defaultPlan);
            $defaultPlan = $plan->id;
        }

        $general = gs();
        $general->site_name = $request->site_name;
        $general->cur_text = $request->cur_text;
        $general->cur_sym = $request->cur_sym;
        $general->base_color = $request->base_color;
        $general->secondary_color = $request->secondary_color;
        $general->registration_bonus = $request->registration_bonus;
        $general->default_plan = $defaultPlan;
        // Handle wallet header image effect config
        $general->wallet_image_effect = $request->wallet_image_effect;

        // Ensure wallet header directory exists
        $walletHeaderPath = getFilePath('walletHeader');
        if (!file_exists($walletHeaderPath)) {
            makeDirectory($walletHeaderPath);
        }

        // Remove selected images
        $existingImages = $general->wallet_images ? (is_array($general->wallet_images) ? $general->wallet_images : json_decode($general->wallet_images, true)) : [];
        if ($request->remove_wallet_images) {
            foreach ($request->remove_wallet_images as $old) {
                try {
                    fileManager()->removeFile(getFilePath('walletHeader') . '/' . $old);
                    // Also remove thumbnails and background variants
                    @unlink(getFilePath('walletHeader') . '/thumb_' . $old);
                    @unlink(getFilePath('walletHeader') . '/bg_sm_' . $old);
                    @unlink(getFilePath('walletHeader') . '/bg_md_' . $old);
                    @unlink(getFilePath('walletHeader') . '/bg_lg_' . $old);
                } catch (\Exception $e) {
                    Log::warning('Failed to remove wallet header image: ' . $old . ' - ' . $e->getMessage());
                }
                $existingImages = array_values(array_diff($existingImages, [$old]));
            }
        }

        // Upload new images (generate thumbnail)
        if ($request->hasFile('wallet_images')) {
            foreach ($request->file('wallet_images') as $file) {
                try {
                    // store main image and also generate thumb 110x110
                    $filename = fileUploader($file, getFilePath('walletHeader'), getFileSize('walletHeader'), null, '110x110');
                    // generate responsive background optimized images with prefixes bg_sm_, bg_md_, bg_lg_
                    try {
                        $sizes = [
                            'sm' => '640x360',
                            'md' => '1024x400',
                            'lg' => '1800x600',
                        ];
                        foreach ($sizes as $key => $size) {
                            $bgSize = explode('x', $size);
                            $bgPath = getFilePath('walletHeader') . '/bg_' . $key . '_' . $filename;
                            Image::make($file)->fit($bgSize[0], $bgSize[1])->save($bgPath);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to generate responsive backgrounds for: ' . $filename . ' - ' . $e->getMessage());
                    }
                    $existingImages[] = $filename;
                } catch (\Exception $e) {
                    Log::error('Failed to upload wallet header image: ' . $e->getMessage());
                    $notify[] = ['error', 'Failed to upload one or more wallet images: ' . $e->getMessage()];
                }
            }
        }

        $general->wallet_images = $existingImages;
        $general->wallet_header_slideshow = $request->wallet_header_slideshow ? 1 : 0;

        $general->country_restriction = $request->country_restriction ? 1 : 0;

        $allowedCountriesInput = $request->allowed_countries;
        if (is_array($allowedCountriesInput)) {
            $allowedCountries = array_filter(array_map('trim', $allowedCountriesInput));
        } else {
            $allowedCountries = array_filter(array_map('trim', explode(',', (string)$allowedCountriesInput)));
        }

        $general->allowed_countries = $allowedCountries ? array_values(array_unique($allowedCountries)) : null;

        $general->forced_country_code = $request->forced_country_code ?: null;

        // Premium Referral & Withdrawal Settings
        $general->referral_premium_base_value = $request->referral_premium_base_value ?? 100;
        $general->non_premium_withdraw_limit = $request->non_premium_withdraw_limit ?? 1000;

        // PTC Settings
        $general->ptc_enable_global = $request->ptc_enable_global ? 1 : 0;
        $general->ptc_max_unlock_level = $request->ptc_max_unlock_level ?? 3;

        $general->save();

        $timezoneFile = config_path('timezone.php');
        $content = '<?php $timezone = ' . $request->timezone . ' ?>';
        file_put_contents($timezoneFile, $content);
        $notify[] = ['success', 'General setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function systemConfiguration()
    {
        $pageTitle = 'System Configuration';
        return view('admin.setting.configuration', compact('pageTitle'));
    }

    public function systemConfigurationSubmit(Request $request)
    {
        $general = gs();
        $general->kv = $request->kv ? 1 : 0;
        $general->ev = $request->ev ? 1 : 0;
        $general->en = $request->en ? 1 : 0;
        $general->sv = $request->sv ? 1 : 0;
        $general->sn = $request->sn ? 1 : 0;
        $general->force_ssl = $request->force_ssl ? 1 : 0;
        $general->secure_password = $request->secure_password ? 1 : 0;
        $general->registration = $request->registration ? 1 : 0;
        $general->agree = $request->agree ? 1 : 0;
        $general->save();
        $notify[] = ['success', 'System configuration updated successfully'];
        return back()->withNotify($notify);
    }


    public function logoIcon()
    {
        $pageTitle = 'Logo & Favicon';
        return view('admin.setting.logo_icon', compact('pageTitle'));
    }

    public function logoIconUpdate(Request $request)
    {
        $request->validate([
            'logo' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'favicon' => ['image', new FileTypeValidate(['png'])],
        ]);
        if ($request->hasFile('logo')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->logo)->save($path . '/logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }

        if ($request->hasFile('favicon')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $size = explode('x', getFileSize('favicon'));
                Image::make($request->favicon)->resize($size[0], $size[1])->save($path . '/favicon.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the favicon'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'Logo & favicon updated successfully'];
        return back()->withNotify($notify);
    }

    public function customCss()
    {
        $pageTitle = 'Custom CSS';
        $file = activeTemplate(true) . 'css/custom.css';
        $file_content = @file_get_contents($file);
        return view('admin.setting.custom_css', compact('pageTitle', 'file_content'));
    }


    public function customCssSubmit(Request $request)
    {
        $file = activeTemplate(true) . 'css/custom.css';
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file, $request->css);
        $notify[] = ['success', 'CSS updated successfully'];
        return back()->withNotify($notify);
    }

    public function maintenanceMode()
    {
        $pageTitle = 'Maintenance Mode';
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        return view('admin.setting.maintenance', compact('pageTitle', 'maintenance'));
    }

    public function maintenanceModeSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        $general = gs();
        $general->maintenance_mode = $request->status ? 1 : 0;
        $general->save();

        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        $maintenance->data_values = [
            'description' => $request->description,
        ];
        $maintenance->save();

        $notify[] = ['success', 'Maintenance mode updated successfully'];
        return back()->withNotify($notify);
    }

    public function cookie()
    {
        $pageTitle = 'GDPR Cookie';
        $cookie = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        return view('admin.setting.cookie', compact('pageTitle', 'cookie'));
    }

    public function cookieSubmit(Request $request)
    {
        $request->validate([
            'short_desc' => 'required|string|max:255',
            'description' => 'required',
        ]);
        $cookie = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        $cookie->data_values = [
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'status' => $request->status ? 1 : 0,
        ];
        $cookie->save();
        $notify[] = ['success', 'Cookie policy updated successfully'];
        return back()->withNotify($notify);
    }

    public function adsSetting()
    {
        $pageTitle = 'Advertisements Setting';
        return view('admin.setting.ads', compact('pageTitle'));
    }

    public function adsSettingSubmit(Request $request)
    {
        $general = gs();
        $general->ads_setting = [
            'ad_price' => $request->ad_price,
            'amount_for_user' => $request->amount_for_user,
        ];
        $general->user_ads_post = $request->user_ads_post ? 1 : 0;
        $general->ad_auto_approve = $request->ad_auto_approve ? 1 : 0;
        $general->save();

        $notify[] = ['success', 'Ads setting updated successfully'];
        return back()->withNotify($notify);
    }
}
