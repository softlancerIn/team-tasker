<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', Channels\FirebaseChannel::class];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => 'Ticket Assigned: #'.$this->ticket->id,
            'body' => 'You have been assigned a new ticket: '.$this->ticket->subject,
            'data' => [
                'ticket_id' => (string) $this->ticket->id,
                'action' => 'ticket_assigned',
            ],
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ticket Assigned: #'.$this->ticket->id.' - '.$this->ticket->subject)
            ->line('You have been assigned a new ticket.')
            ->line('Client: '.($this->ticket->user ? $this->ticket->user->name : $this->ticket->email_source))
            ->line('Priority: '.ucfirst($this->ticket->priority))
            ->action('View Ticket', route('admin.tickets.show', $this->ticket->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'message' => 'New ticket assigned: #'.$this->ticket->id,
        ];
    }
}
