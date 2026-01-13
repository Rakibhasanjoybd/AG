<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioPlayer;
use Illuminate\Http\Request;

class ManageAudioPlayerController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Audio Player';
        $audios = AudioPlayer::latest()->paginate(getPaginate());
        return view('admin.audio_player.index', compact('pageTitle', 'audios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'required|mimes:mp3,wav,ogg|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $audio = new AudioPlayer();
        $audio->title = $request->title;
        $audio->autoplay = $request->autoplay ? 1 : 0;
        $audio->loop = $request->loop ? 1 : 0;
        $audio->status = $request->status ? 1 : 0;

        if ($request->hasFile('audio_file')) {
            $audio->audio_file = fileUploader($request->audio_file, 'assets/audio', null);
        }

        if ($request->hasFile('thumbnail')) {
            $audio->thumbnail = fileUploader($request->thumbnail, getFilePath('audioPlayer'), getFileSize('audioPlayer'));
        }

        $audio->save();

        $notify[] = ['success', 'Audio created successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'nullable|mimes:mp3,wav,ogg|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $audio = AudioPlayer::findOrFail($id);
        $audio->title = $request->title;
        $audio->autoplay = $request->autoplay ? 1 : 0;
        $audio->loop = $request->loop ? 1 : 0;
        $audio->status = $request->status ? 1 : 0;

        if ($request->hasFile('audio_file')) {
            $old = $audio->audio_file;
            $audio->audio_file = fileUploader($request->audio_file, 'assets/audio', null, $old);
        }

        if ($request->hasFile('thumbnail')) {
            $old = $audio->thumbnail;
            $audio->thumbnail = fileUploader($request->thumbnail, getFilePath('audioPlayer'), getFileSize('audioPlayer'), $old);
        }

        $audio->save();

        $notify[] = ['success', 'Audio updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $audio = AudioPlayer::findOrFail($id);
        fileManager()->removeFile('assets/audio/' . $audio->audio_file);
        fileManager()->removeFile(getFilePath('audioPlayer') . '/' . $audio->thumbnail);
        $audio->delete();

        $notify[] = ['success', 'Audio deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $audio = AudioPlayer::findOrFail($id);
        $audio->status = !$audio->status;
        $audio->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
