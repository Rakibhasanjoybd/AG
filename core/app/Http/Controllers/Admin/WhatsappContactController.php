<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsappContactController extends Controller
{
    /**
     * Display a listing of the WhatsApp contacts.
     */
    public function index()
    {
        $pageTitle = 'WhatsApp Customer Care Contacts';
        $contacts = WhatsappContact::orderBy('display_order', 'asc')->get();

        return view('admin.whatsapp_contacts.index', compact('pageTitle', 'contacts'));
    }

    /**
     * Show the form for creating a new WhatsApp contact.
     */
    public function create()
    {
        $pageTitle = 'Add WhatsApp Contact';
        return view('admin.whatsapp_contacts.create', compact('pageTitle'));
    }

    /**
     * Store a newly created WhatsApp contact in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'description' => 'nullable|string',
            'message_format' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'display_order' => 'nullable|integer',
        ]);

        $data = $request->except(['profile_image', '_token', 'is_active']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['display_order'] = $request->display_order ?? 0;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $directory = base_path('../assets/images/whatsapp');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image->move($directory, $filename);
            $data['profile_image'] = $filename;
        }

        WhatsappContact::create($data);

        $notify[] = ['success', 'WhatsApp contact created successfully'];
        return redirect()->route('admin.whatsapp.contacts.index')->withNotify($notify);
    }

    /**
     * Show the form for editing the specified WhatsApp contact.
     */
    public function edit($id)
    {
        $pageTitle = 'Edit WhatsApp Contact';
        $contact = WhatsappContact::findOrFail($id);

        return view('admin.whatsapp_contacts.edit', compact('pageTitle', 'contact'));
    }

    /**
     * Update the specified WhatsApp contact in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'description' => 'nullable|string',
            'message_format' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'display_order' => 'nullable|integer',
        ]);

        $contact = WhatsappContact::findOrFail($id);
        $data = $request->except(['profile_image', '_token', '_method', 'is_active']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($contact->profile_image) {
                $oldImagePath = base_path('../assets/images/whatsapp/' . $contact->profile_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('profile_image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $directory = base_path('../assets/images/whatsapp');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image->move($directory, $filename);
            $data['profile_image'] = $filename;
        }

        $contact->update($data);

        $notify[] = ['success', 'WhatsApp contact updated successfully'];
        return redirect()->route('admin.whatsapp.contacts.index')->withNotify($notify);
    }

    /**
     * Remove the specified WhatsApp contact from storage.
     */
    public function destroy($id)
    {
        $contact = WhatsappContact::findOrFail($id);

        // Delete profile image
        if ($contact->profile_image) {
            $imagePath = base_path('../assets/images/whatsapp/' . $contact->profile_image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $contact->delete();

        $notify[] = ['success', 'WhatsApp contact deleted successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Toggle the active status of a WhatsApp contact.
     */
    public function toggleStatus($id)
    {
        $contact = WhatsappContact::findOrFail($id);
        $contact->is_active = !$contact->is_active;
        $contact->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}

