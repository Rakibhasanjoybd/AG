<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailySpotlight;
use Illuminate\Http\Request;

class ManageDailySpotlightController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Daily Spotlights';
        $spotlights = DailySpotlight::ordered()->paginate(getPaginate());
        return view('admin.daily_spotlight.index', compact('pageTitle', 'spotlights'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $spotlight = new DailySpotlight();
        $spotlight->title = $request->title;
        $spotlight->link = $request->link;
        $spotlight->description = $request->description;
        $spotlight->order = $request->order;
        $spotlight->status = $request->status ? 1 : 0;

        if ($request->hasFile('image')) {
            $spotlight->image = fileUploader($request->image, getFilePath('spotlight'), getFileSize('spotlight'));
        }

        $spotlight->save();

        $notify[] = ['success', 'Daily spotlight created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $spotlight = DailySpotlight::findOrFail($id);
        $spotlight->title = $request->title;
        $spotlight->link = $request->link;
        $spotlight->description = $request->description;
        $spotlight->order = $request->order;
        $spotlight->status = $request->status ? 1 : 0;

        if ($request->hasFile('image')) {
            $old = $spotlight->image;
            $spotlight->image = fileUploader($request->image, getFilePath('spotlight'), getFileSize('spotlight'), $old);
        }

        $spotlight->save();

        $notify[] = ['success', 'Daily spotlight updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $spotlight = DailySpotlight::findOrFail($id);
        fileManager()->removeFile(getFilePath('spotlight') . '/' . $spotlight->image);
        $spotlight->delete();

        $notify[] = ['success', 'Daily spotlight deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $spotlight = DailySpotlight::findOrFail($id);
        $spotlight->status = !$spotlight->status;
        $spotlight->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
