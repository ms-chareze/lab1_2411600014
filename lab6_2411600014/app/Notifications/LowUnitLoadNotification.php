<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Lab 6 "LowStockNotification": sent when a student's enrolled units fall
 * to or below their minimum load. Queued, so run `php artisan queue:work`.
 */
class LowUnitLoadNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student)
    {
        // Only dispatch after the surrounding DB transaction commits.
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->student;

        return (new MailMessage)
            ->subject("Low Unit Load: {$student->name} ({$student->student_id})")
            ->greeting('Low unit load alert')
            ->line("{$student->name} ({$student->student_id}) is enrolled in only {$student->enrolled_units} unit(s).")
            ->line("Minimum load: {$student->min_units} unit(s).")
            ->line('Suggested action: review the enrollment and add units if appropriate.')
            ->action('Review Student Record', url(route('students.show', $student->id, false)));
    }

    /** Stored in notifications.data (JSON) for the database channel. */
    public function toArray(object $notifiable): array
    {
        $student = $this->student;

        return [
            'student_id'     => $student->id,
            'student_number' => $student->student_id,
            'student_name'   => $student->name,
            'enrolled_units' => $student->enrolled_units,
            'min_units'      => $student->min_units,
            'message'        => "{$student->name} is down to {$student->enrolled_units} unit(s) (minimum {$student->min_units}).",
            'action'         => 'Review enrollment and add units',
            'url'            => route('students.show', $student->id, false),
        ];
    }
}
