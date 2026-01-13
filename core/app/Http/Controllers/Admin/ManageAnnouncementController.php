<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class ManageAnnouncementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Announcements';
        $announcements = Announcement::latest()->paginate(getPaginate());
        return view('admin.announcement.index', compact('pageTitle', 'announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scroll_speed' => 'required|integer|min:10|max:200',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'scroll_speed' => $request->scroll_speed,
            'status' => $request->status ? 1 : 0,
        ]);

        $notify[] = ['success', 'Announcement created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scroll_speed' => 'required|integer|min:10|max:200',
        ]);

        $announcement = Announcement::findOrFail($id);
        $announcement->title = $request->title;
        $announcement->content = $request->content;
        $announcement->scroll_speed = $request->scroll_speed;
        $announcement->status = $request->status ? 1 : 0;
        $announcement->save();

        $notify[] = ['success', 'Announcement updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        Announcement::findOrFail($id)->delete();
        $notify[] = ['success', 'Announcement deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->status = !$announcement->status;
        $announcement->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
