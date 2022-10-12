<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class MessageSent extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private array $data)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return [OneSignalChannel::class];
    }

    public function toOneSignal()
    {
        $messageData = $this->data['messageData'];
        return OneSignalMessage::create()
            ->setSubject($messageData['senderName']."sent you message")
            ->setData($messageData['message'])
            ->setData('data', $messageData);
    }
}
