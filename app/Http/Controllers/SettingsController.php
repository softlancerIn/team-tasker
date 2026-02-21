<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function general()
    {
        return view('admin.settings.general');
    }

    public function email()
    {
        // Fetch all settings
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.email', compact('settings'));
    }

    public function statuses()
    {
        // Fetch existing statuses
        $statuses = Status::orderBy('order')->get();
        return view('admin.settings.statuses', compact('statuses'));
    }

    public function storeGeneral(Request $request)
    {
        // Future General Settings Logic
        return back()->with('success', 'General settings updated.');
    }

    public function storeEmail(Request $request)
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
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Email settings updated successfully.');
    }

    // Status Management
    public function storeStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        $maxOrder = Status::max('order') ?? 0;

        Status::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Status created successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'order' => 'numeric',
        ]);

        $status = Status::findOrFail($id);
        $status->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
            'order' => $request->order ?? $status->order,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }

    public function destroyStatus($id)
    {
        $status = Status::findOrFail($id);

        if ($status->tasks()->count() > 0) {
            return back()->with('error', 'Cannot delete status with associated tasks.');
        }

        if ($status->is_default) {
            return back()->with('error', 'Cannot delete default status.');
        }

        $status->delete();

        return back()->with('success', 'Status deleted successfully.');
    }
}
