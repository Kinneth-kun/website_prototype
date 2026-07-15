<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewInquiryReceived extends Notification
{
    use Queueable;

    public function __construct(private readonly array $inquiry)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customerName = (string) $this->inquiry['name'];
        $inquiryType = (string) $this->inquiry['inquiry_type'];
        $subject = trim((string) ($this->inquiry['subject'] ?? ''));

        return [
            'kind' => 'new_inquiry',
            'title' => 'New inquiry from '.$customerName,
            'message' => $subject !== ''
                ? Str::limit($subject, 160)
                : $inquiryType.' inquiry received.',
            'inquiry_id' => $this->inquiry['id'],
            'reference_number' => $this->inquiry['reference_number'],
            'inquiry_type' => $inquiryType,
            'customer_name' => $customerName,
            'action_url' => '/admin/manage/inquiries',
        ];
    }
}
