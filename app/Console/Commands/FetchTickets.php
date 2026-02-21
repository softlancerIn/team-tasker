<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails from configured IMAP and convert to tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!function_exists('imap_open')) {
            $this->error('PHP IMAP extension is missing. Please enable it to fetch emails.');
            Log::error('FetchTickets: PHP IMAP extension missing.');
            return 1;
        }

        $host = Setting::where('key', 'imap_host')->value('value');
        $port = Setting::where('key', 'imap_port')->value('value');
        $user = Setting::where('key', 'imap_user')->value('value');
        $pass = Setting::where('key', 'imap_password')->value('value');
        $enc = Setting::where('key', 'imap_encryption')->value('value'); // ssl, tls, or null

        if (!$host || !$user || !$pass) {
            $this->error('IMAP settings not configured.');
            return 1;
        }

        // Construct mailbox string
        // Example: {imap.gmail.com:993/imap/ssl}INBOX
        $protocol = '/imap';
        if ($enc == 'ssl') {
            $protocol .= '/ssl';
        } elseif ($enc == 'tls') {
             $protocol .= '/tls';
        } else {
             $protocol .= '/notls';
        }
        
        $mailbox = "{{$host}:{$port}{$protocol}}INBOX";

        $this->info("Connecting to $mailbox as $user...");

        try {
            // Suppress warnings for connection errors
            $inbox = @imap_open($mailbox, $user, $pass);
        } catch (\Throwable $e) {
            $this->error('Connection failed: ' . $e->getMessage());
            Log::error('FetchTickets: ' . $e->getMessage());
            return 1;
        }

        if (!$inbox) {
             $this->error('Connection failed: ' . imap_last_error());
             return 1;
        }

        $emails = imap_search($inbox, 'UNSEEN');

        if (!$emails) {
            $this->info('No new emails found.');
            imap_close($inbox);
            return 0;
        }

        $this->info('Found ' . count($emails) . ' new emails.');

        foreach ($emails as $emailId) {
            $header = imap_headerinfo($inbox, $emailId);
            $subject = isset($header->subject) ? $header->subject : '(No Subject)';
            $fromEmail = $header->from[0]->mailbox . "@" . $header->from[0]->host;
            
            // Should properly decode subject/body if encoded, keeping it simple for now
            // $structure = imap_fetchstructure($inbox, $emailId);
            $body = imap_fetchbody($inbox, $emailId, 1); // Get body section 1 (usually plain text if multipart)
            
            // Basic cleanup of body
            $body = trim($body);
            if (!$body) {
                // Try section 1.1 if 1 failed (e.g. multipart/alternative)
                $body = imap_fetchbody($inbox, $emailId, 1.1);
            }

            // Check if reply to existing ticket
            if (preg_match('/#(\d+)/', $subject, $matches)) {
                $ticketId = $matches[1];
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    $this->info("Processing reply to Ticket #$ticketId from $fromEmail");
                    
                    // Identify user
                    $user = User::where('email', $fromEmail)->first();

                    $ticket->replies()->create([
                        'user_id' => $user ? $user->id : null,
                        'body' => $body ?: '(Empty Body)',
                        'type' => 'client_reply',
                    ]);

                    if($ticket->status == 'closed' || $ticket->status == 'resolved') {
                        $ticket->update(['status' => 'open']);
                    }
                    continue;
                }
            }

            // Create new ticket
            $this->info("Creating new ticket from $fromEmail: $subject");
            
            $user = User::where('email', $fromEmail)->first();

            Ticket::create([
                'user_id' => $user ? $user->id : null,
                'email_source' => $fromEmail,
                'subject' => $subject,
                'body' => $body ?: '(Empty Body)',
                'priority' => 'low',
                'status' => 'open',
            ]);
        }

        imap_close($inbox);
        $this->info('Done.');
        return 0;
    }
}
