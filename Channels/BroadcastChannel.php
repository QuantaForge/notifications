<?php

namespace QuantaForge\Notifications\Channels;

use QuantaForge\Contracts\Events\Dispatcher;
use QuantaForge\Notifications\Events\BroadcastNotificationCreated;
use QuantaForge\Notifications\Messages\BroadcastMessage;
use QuantaForge\Notifications\Notification;
use RuntimeException;

class BroadcastChannel
{
    /**
     * The event dispatcher.
     *
     * @var \QuantaForge\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new broadcast channel.
     *
     * @param  \QuantaForge\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \QuantaForge\Notifications\Notification  $notification
     * @return array|null
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $this->getData($notifiable, $notification);

        $event = new BroadcastNotificationCreated(
            $notifiable, $notification, is_array($message) ? $message : $message->data
        );

        if ($message instanceof BroadcastMessage) {
            $event->onConnection($message->connection)
                  ->onQueue($message->queue);
        }

        return $this->events->dispatch($event);
    }

    /**
     * Get the data for the notification.
     *
     * @param  mixed  $notifiable
     * @param  \QuantaForge\Notifications\Notification  $notification
     * @return mixed
     *
     * @throws \RuntimeException
     */
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toBroadcast')) {
            return $notification->toBroadcast($notifiable);
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toArray method.');
    }
}
