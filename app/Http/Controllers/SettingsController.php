<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Status;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return view('admin.settings.general', compact('settings'));
    }

    public function autostop()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return view('admin.settings.autostop', compact('settings'));
    }

    public function storeAutostop(Request $request)
    {
        $request->validate([
            'auto_stop_timers' => 'required|in:yes,no',
            'office_close_time' => 'required_if:auto_stop_timers,yes',
        ]);

        Setting::updateOrCreate(['key' => 'auto_stop_timers'], ['value' => $request->auto_stop_timers]);
        if ($request->has('office_close_time')) {
            Setting::updateOrCreate(['key' => 'office_close_time'], ['value' => $request->office_close_time]);
        }

        return back()->with('success', 'Auto Stop Timer settings updated successfully.');
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
        $statuses = Status::orderBy('order')->paginate(15);

        return view('admin.settings.statuses', compact('statuses'));
    }

    public function storeGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        if ($request->filled('app_name')) {
            Setting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name]
            );
        }

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('logos', 'public');
            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $path]
            );
        }

        return back()->with('success', 'General settings updated successfully.');
    }

    public function storeEmail(Request $request)
    {
        $data = $request->validate([
            'imap_host' => 'nullable|string',
            'imap_port' => 'nullable|integer',
            'imap_user' => 'nullable|string',
            'imap_password' => 'nullable|string',
            'imap_encryption' => 'nullable|string|in:ssl,tls,null',

            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_user' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,null',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);

        // Validate SMTP connectivity if host is provided
        if (! empty($data['smtp_host'])) {
            try {
                $this->testSmtpConnection($data);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['smtp_host' => 'SMTP Connection failed: '.$e->getMessage()]);
            }
        }

        // Validate IMAP connectivity
        if (! empty($data['imap_host'])) {
            try {
                $this->testImapConnection($data);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['imap_host' => 'IMAP Connection failed: '.$e->getMessage()]);
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Email settings validated and updated successfully.');
    }

    private function testSmtpConnection($config)
    {
        $encryption = $config['smtp_encryption'] ?? 'null';
        $scheme = 'smtp';

        if ($encryption === 'ssl') {
            $scheme = 'smtps';
        }

        $dsn = sprintf(
            '%s://%s:%s@%s:%s',
            $scheme,
            urlencode($config['smtp_user'] ?? ''),
            urlencode($config['smtp_password'] ?? ''),
            $config['smtp_host'],
            $config['smtp_port'] ?? 587
        );

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email)
            ->from($config['from_email'] ?? 'test@example.com')
            ->to('test@example.com')
            ->subject('SMTP Connection Test')
            ->text('Checking connection...');

        // We don't actually send, just verify the transport can connect
        // Symfony Mailer doesn't have a direct 'connect' method in TransportInterface
        // But attempt to send a dummy message will trigger connection
        // Alternatively, use EsmtpTransport specifically if available to call start()
        if (method_exists($transport, 'start')) {
            $transport->start();
            $transport->stop();
        } else {
            // For general transports, we might need a dummy send or better verification
            // Since Esmtp is almost certainly used for 'smtp://' DSN:
            try {
                $transport->send($email);
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                // If it's just a transport error (like connection refused), throw it
                throw $e;
            } catch (\Exception $e) {
                // Ignore errors related to local sending if connection worked
                if (str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'could not connect')) {
                    throw $e;
                }
            }
        }
    }

    private function testImapConnection($config)
    {
        $encryption = $config['imap_encryption'] ?? 'null';

        $client = \Webklex\IMAP\Facades\Client::make([
            'host' => $config['imap_host'],
            'port' => $config['imap_port'],
            'encryption' => $encryption === 'null' ? false : $encryption,
            'validate_cert' => false, // Set to false for easier initial setup, or true for production
            'username' => $config['imap_user'],
            'password' => $config['imap_password'],
            'protocol' => 'imap',
        ]);

        $client->connect();
        $client->disconnect();
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

    // Tag Management
    public function tags()
    {
        $tags = Tag::all();

        return view('admin.settings.tags', compact('tags'));
    }

    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
        ]);

        return back()->with('success', 'Tag created successfully.');
    }

    public function updateTag(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        $tag = Tag::findOrFail($id);
        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
        ]);

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroyTag($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }
}
