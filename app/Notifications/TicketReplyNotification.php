<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    public $reply;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketReply $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Ticket #'.$this->ticket->id.'] New Reply: '.$this->ticket->subject)
            ->line('A new reply has been posted to your ticket.')
            ->line('Replied By: '.($this->reply->user ? $this->reply->user->name : 'Support Team'))
            ->line('---')
            ->line(strip_tags($this->reply->body)) // Strip HTML for plain text email, or keep raw? Maybe limit length.
            ->line('---')
            ->action('View Ticket', route('client.tickets.show', $this->ticket->id))
            ->line('You can also reply directly to this email.');
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
            'message' => 'New reply on ticket #'.$this->ticket->id,
        ];
    }
}
