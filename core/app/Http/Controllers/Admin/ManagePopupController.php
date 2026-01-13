<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupAnnouncement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ManagePopupController extends Controller
{
    public function index()
    {
        $pageTitle = 'Popup Announcements';
        $popups = PopupAnnouncement::with('targetUsers')
            ->latest()
            ->paginate(getPaginate());
        return view('admin.popup.index', compact('pageTitle', 'popups'));
    }

    public function create()
    {
        $pageTitle = 'Create Popup Announcement';
        $users = User::where('status', 1)->orderBy('username')->get(['id', 'username', 'fullname', 'email']);
        return view('admin.popup.create', compact('pageTitle', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'media' => 'nullable|mimes:jpeg,jpg,png,gif,webp,mp4,webm,mov,avi,mkv|max:10240',
            'media_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'target_type' => 'required|in:all,specific',
            'target_users' => 'required_if:target_type,specific|array',
            'target_users.*' => 'exists:users,id',
            'show_to_guests' => 'nullable|boolean',
            'show_once' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $popup = new PopupAnnouncement();
        $popup->title = $request->title;
        $popup->content = $request->content;
        $popup->button_text = $request->button_text;
        $popup->button_link = $request->button_link;
        $popup->target_type = $request->target_type;
        $popup->show_to_guests = $request->show_to_guests ? 1 : 0;
        $popup->show_once = $request->show_once ? 1 : 0;
        $popup->priority = $request->priority ?? 0;
        $popup->start_date = $request->start_date;
        $popup->end_date = $request->end_date;
        $popup->status = $request->status ? 1 : 0;

        // Handle media upload (image or video) or external URL
        if ($request->hasFile('media')) {
            $popup->image = $this->uploadMedia($request->file('media'));
        } elseif ($request->filled('media_url')) {
            $popup->image = $request->media_url;
        }

        $popup->save();

        // Attach target users if specific
        if ($request->target_type === 'specific' && !empty($request->target_users)) {
            $popup->targetUsers()->attach($request->target_users);
        }

        $notify[] = ['success', 'Popup announcement created successfully'];
        return redirect()->route('admin.popup.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Popup Announcement';
        $popup = PopupAnnouncement::with('targetUsers')->findOrFail($id);
        $users = User::where('status', 1)->orderBy('username')->get(['id', 'username', 'fullname', 'email']);
        $selectedUsers = $popup->targetUsers->pluck('id')->toArray();
        return view('admin.popup.edit', compact('pageTitle', 'popup', 'users', 'selectedUsers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'media' => 'nullable|mimes:jpeg,jpg,png,gif,webp,mp4,webm,mov,avi,mkv|max:10240',
            'media_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'target_type' => 'required|in:all,specific',
            'target_users' => 'required_if:target_type,specific|array',
            'target_users.*' => 'exists:users,id',
            'show_to_guests' => 'nullable|boolean',
            'show_once' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $popup = PopupAnnouncement::findOrFail($id);
        $popup->title = $request->title;
        $popup->content = $request->content;
        $popup->button_text = $request->button_text;
        $popup->button_link = $request->button_link;
        $popup->target_type = $request->target_type;
        $popup->show_to_guests = $request->show_to_guests ? 1 : 0;
        $popup->show_once = $request->show_once ? 1 : 0;
        $popup->priority = $request->priority ?? 0;
        $popup->start_date = $request->start_date;
        $popup->end_date = $request->end_date;
        $popup->status = $request->status ? 1 : 0;

        // Handle media upload (image or video) or external URL
        if ($request->hasFile('media')) {
            // Delete old media if it's a local file
            if ($popup->image && !$this->isExternalUrl($popup->image)) {
                $this->deleteMedia($popup->image);
            }
            $popup->image = $this->uploadMedia($request->file('media'));
        } elseif ($request->filled('media_url')) {
            // Delete old media if it's a local file
            if ($popup->image && !$this->isExternalUrl($popup->image)) {
                $this->deleteMedia($popup->image);
            }
            $popup->image = $request->media_url;
        }

        // Handle media removal
        if ($request->remove_media && $popup->image) {
            if (!$this->isExternalUrl($popup->image)) {
                $this->deleteMedia($popup->image);
            }
            $popup->image = null;
        }

        $popup->save();

        // Sync target users
        if ($request->target_type === 'specific') {
            $popup->targetUsers()->sync($request->target_users ?? []);
        } else {
            $popup->targetUsers()->detach();
        }

        $notify[] = ['success', 'Popup announcement updated successfully'];
        return redirect()->route('admin.popup.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $popup = PopupAnnouncement::findOrFail($id);
        
        // Delete media if exists
        if ($popup->image) {
            $this->deleteMedia($popup->image);
        }
        
        $popup->delete();

        $notify[] = ['success', 'Popup announcement deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $popup = PopupAnnouncement::findOrFail($id);
        $popup->status = !$popup->status;
        $popup->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }

    public function duplicate($id)
    {
        $original = PopupAnnouncement::with('targetUsers')->findOrFail($id);
        
        $popup = $original->replicate();
        $popup->title = $popup->title . ' (Copy)';
        $popup->status = 0; // Set as inactive
        $popup->save();

        // Copy target users
        if ($original->target_type === 'specific') {
            $popup->targetUsers()->attach($original->targetUsers->pluck('id'));
        }

        $notify[] = ['success', 'Popup announcement duplicated successfully'];
        return back()->withNotify($notify);
    }

    public function resetViews($id)
    {
        $popup = PopupAnnouncement::findOrFail($id);
        $popup->viewedByUsers()->detach();

        $notify[] = ['success', 'Popup views reset successfully. Users will see this popup again.'];
        return back()->withNotify($notify);
    }

    public function statistics($id)
    {
        $pageTitle = 'Popup Statistics';
        $popup = PopupAnnouncement::withCount('viewedByUsers')->findOrFail($id);
        $views = $popup->viewedByUsers()->latest('popup_announcement_views.created_at')->paginate(getPaginate());
        
        return view('admin.popup.statistics', compact('pageTitle', 'popup', 'views'));
    }

    /**
     * Upload media (image or video) to the popup directory
     */
    private function uploadMedia($file): string
    {
        // Direct path to root assets folder (outside /core)
        $fullPath = dirname(base_path()) . '/assets/images/popup';
        
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Sanitize filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalName);
        $extension = strtolower($file->getClientOriginalExtension());
        
        $filename = uniqid() . '_' . time() . '_' . substr(md5(microtime()), 0, 8) . '.' . $extension;
        $file->move($fullPath, $filename);
        
        return $filename;
    }

    /**
     * Delete media from the popup directory
     */
    private function deleteMedia($filename): void
    {
        // Root public path
        $path = dirname(base_path()) . '/assets/images/popup/' . $filename;
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * Check if the given string is an external URL
     */
    private function isExternalUrl($string): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get media type (image or video)
     */
    public function getMediaType($filename): string
    {
        $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return in_array($extension, $videoExtensions) ? 'video' : 'image';
    }
}
