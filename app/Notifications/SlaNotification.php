<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    protected $type; // 'warning' or 'breach'

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $type)
    {
        $this->task = $task;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'warning'
            ? "Upcoming Deadline: Task #{$this->task->id}"
            : "SLA BREACH: Task #{$this->task->id} is Overdue!";

        $line = $this->type === 'warning'
            ? "The task '{$this->task->title}' is approaching its deadline."
            : "The task '{$this->task->title}' has passed its deadline and is now marked as an SLA breach.";

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->action('View Task Details', url('/admin/tasks/details/'.$this->task->id))
            ->line('Please take action as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'type' => $this->type,
            'message' => $this->type === 'warning'
                ? 'Warning: Task approaching deadline'
                : 'Breach: Task is overdue',
        ];
    }
}
