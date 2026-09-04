<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Task $task,
        public string $reminderType,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->email_enabled ? ['mail'] : [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->task->deadline->format('d M Y, H:i');

        return (new MailMessage)
            ->subject('Reminder: "'.$this->task->title.'"')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Task kamu akan mencapai deadline dalam waktu dekat.')
            ->line('Judul task: **'.$this->task->title.'**')
            ->line('Deadline: **'.$deadline.'**')
            ->line($this->reminderLine())
            ->action('Lihat Task', route('tasks.show', $this->task));
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
            'reminder_type' => $this->reminderType,
            'deadline' => $this->task->deadline->toDateTimeString(),
        ];
    }

    private function reminderLine(): string
    {
        return match ($this->reminderType) {
            '24h' => 'Reminder 24 jam sebelum deadline.',
            '1h' => 'Reminder 1 jam sebelum deadline.',
            'overdue' => 'Task ini telah melewati deadline.',
            default => 'Ini pengingat deadline.',
        };
    }
}
