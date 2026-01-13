<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoTutorial;
use Illuminate\Http\Request;

class ManageVideoTutorialController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Video Tutorials';
        $tutorials = VideoTutorial::ordered()->paginate(getPaginate());
        return view('admin.video_tutorial.index', compact('pageTitle', 'tutorials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'lesson_number' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $tutorial = new VideoTutorial();
        $tutorial->title = $request->title;
        $tutorial->video_url = $request->video_url;
        $tutorial->description = $request->description;
        $tutorial->lesson_number = $request->lesson_number;
        $tutorial->order = $request->order;
        $tutorial->status = $request->status ? 1 : 0;

        if ($request->hasFile('thumbnail')) {
            $tutorial->thumbnail = fileUploader($request->thumbnail, getFilePath('tutorial'), getFileSize('tutorial'));
        }

        $tutorial->save();

        $notify[] = ['success', 'Video tutorial created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'lesson_number' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $tutorial = VideoTutorial::findOrFail($id);
        $tutorial->title = $request->title;
        $tutorial->video_url = $request->video_url;
        $tutorial->description = $request->description;
        $tutorial->lesson_number = $request->lesson_number;
        $tutorial->order = $request->order;
        $tutorial->status = $request->status ? 1 : 0;

        if ($request->hasFile('thumbnail')) {
            $old = $tutorial->thumbnail;
            $tutorial->thumbnail = fileUploader($request->thumbnail, getFilePath('tutorial'), getFileSize('tutorial'), $old);
        }

        $tutorial->save();

        $notify[] = ['success', 'Video tutorial updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $tutorial = VideoTutorial::findOrFail($id);
        fileManager()->removeFile(getFilePath('tutorial') . '/' . $tutorial->thumbnail);
        $tutorial->delete();

        $notify[] = ['success', 'Video tutorial deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $tutorial = VideoTutorial::findOrFail($id);
        $tutorial->status = !$tutorial->status;
        $tutorial->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
