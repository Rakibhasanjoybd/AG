<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppDownloadController extends Controller
{
    public function index()
    {
        $pageTitle = 'App Download Management';
        $android = AppDownload::where('platform', 'android')->first();
        $ios = AppDownload::where('platform', 'ios')->first();
        return view('admin.app_download.index', compact('pageTitle', 'android', 'ios'));
    }

    public function update(Request $request, $platform)
    {
        $request->validate([
            'version' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'file' => $platform == 'android' ? 'nullable|file|mimes:apk|max:102400' : 'nullable|file|mimes:mobileconfig,plist|max:10240',
            'force_update' => 'nullable|boolean',
        ]);

        $appDownload = AppDownload::firstOrCreate(
            ['platform' => $platform],
            ['status' => 1]
        );

        $appDownload->version = $request->version;
        $appDownload->description = $request->description;
        $appDownload->force_update = $request->has('force_update') ? 1 : 0;

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Delete old file if exists
            if ($appDownload->file_path && File::exists(public_path($appDownload->file_path))) {
                File::delete(public_path($appDownload->file_path));
            }

            // Create directory if not exists
            $directory = 'assets/apps/' . $platform;
            if (!File::exists(public_path($directory))) {
                File::makeDirectory(public_path($directory), 0755, true);
            }

            // Generate unique filename
            $fileName = $platform == 'android'
                ? 'agco_finance_v' . str_replace('.', '_', $request->version) . '.apk'
                : 'agco_finance_ios_profile.mobileconfig';

            $file->move(public_path($directory), $fileName);

            $appDownload->file_name = $fileName;
            $appDownload->file_path = $directory . '/' . $fileName;
            $appDownload->file_size = File::size(public_path($directory . '/' . $fileName));
        }

        $appDownload->save();

        $notify[] = ['success', ucfirst($platform) . ' app updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($platform)
    {
        $appDownload = AppDownload::where('platform', $platform)->first();

        if (!$appDownload) {
            $notify[] = ['error', 'App not found'];
            return back()->withNotify($notify);
        }

        $appDownload->status = !$appDownload->status;
        $appDownload->save();

        $notify[] = ['success', ucfirst($platform) . ' app ' . ($appDownload->status ? 'enabled' : 'disabled') . ' successfully'];
        return back()->withNotify($notify);
    }

    public function delete($platform)
    {
        $appDownload = AppDownload::where('platform', $platform)->first();

        if (!$appDownload) {
            $notify[] = ['error', 'App not found'];
            return back()->withNotify($notify);
        }

        // Delete file if exists
        if ($appDownload->file_path && File::exists(public_path($appDownload->file_path))) {
            File::delete(public_path($appDownload->file_path));
        }

        $appDownload->delete();

        $notify[] = ['success', ucfirst($platform) . ' app deleted successfully'];
        return back()->withNotify($notify);
    }
}
