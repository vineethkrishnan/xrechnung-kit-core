<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;

/**
 * Wraps any callable as a notification channel. Use this for one-off integrations
 * (a closure that pushes to a queue, posts to PagerDuty, opens a Linear issue, etc.)
 * without writing a dedicated channel class.
 */
final class CallableChannel implements NotificationChannelInterface
{
    /** @var \Closure(Notification): void */
    private \Closure $sender;

    /**
     * @param callable(Notification): void $sender
     */
    public function __construct(
        callable $sender,
        private readonly string $name = 'callable'
    ) {
        $this->sender = \Closure::fromCallable($sender);
    }

    #[\Override]
    public function send(Notification $notification): void
    {
        try {
            ($this->sender)($notification);
        } catch (\Throwable) {
            // best-effort delivery
        }
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
