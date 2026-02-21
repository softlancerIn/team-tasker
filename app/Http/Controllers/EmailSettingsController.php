<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class EmailSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('key', [
            'imap_host', 'imap_port', 'imap_user', 'imap_password', 'imap_encryption',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_password', 'smtp_encryption', 'from_email', 'from_name'
        ])->pluck('value', 'key');

        return view('admin.settings.email', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_user' => 'required|string',
            'imap_password' => 'required|string',
            'imap_encryption' => 'nullable|string|in:ssl,tls,null',
            
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_user' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,null',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Email settings updated successfully.');
    }
}
