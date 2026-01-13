<?php

namespace App\Http\Controllers;

use App\Models\AppDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppDownloadController extends Controller
{
    public function download(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        // Detect platform from User-Agent
        $isIOS = preg_match('/iPhone|iPad|iPod/i', $userAgent);
        $isAndroid = preg_match('/Android/i', $userAgent);

        if ($isIOS) {
            return $this->downloadPlatform('ios');
        } elseif ($isAndroid) {
            return $this->downloadPlatform('android');
        }

        // Default to Android for unknown devices
        return $this->downloadPlatform('android');
    }

    public function downloadPlatform($platform)
    {
        $platform = strtolower($platform);

        if (!in_array($platform, ['android', 'ios'])) {
            return back()->with('error', 'Invalid platform');
        }

        $app = AppDownload::where('platform', $platform)->active()->first();

        if (!$app || !$app->file_path) {
            return back()->with('error', 'App not available for ' . ucfirst($platform));
        }

        $filePath = public_path($app->file_path);

        if (!File::exists($filePath)) {
            return back()->with('error', 'File not found');
        }

        // Increment download count
        $app->increment('download_count');

        // Set appropriate content type
        if ($platform == 'android') {
            $contentType = 'application/vnd.android.package-archive';
        } else {
            $contentType = 'application/x-apple-aspen-config';
        }

        return response()->download($filePath, $app->file_name, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $app->file_name . '"'
        ]);
    }

    public function info()
    {
        $android = AppDownload::where('platform', 'android')->active()->first();
        $ios = AppDownload::where('platform', 'ios')->active()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'android' => $android ? [
                    'available' => true,
                    'version' => $android->version,
                    'description' => $android->description,
                    'file_size' => $android->file_size_formatted,
                    'force_update' => $android->force_update,
                    'download_url' => route('app.download.platform', 'android'),
                ] : ['available' => false],
                'ios' => $ios ? [
                    'available' => true,
                    'version' => $ios->version,
                    'description' => $ios->description,
                    'file_size' => $ios->file_size_formatted,
                    'force_update' => $ios->force_update,
                    'download_url' => route('app.download.platform', 'ios'),
                ] : ['available' => false],
            ]
        ]);
    }
}
