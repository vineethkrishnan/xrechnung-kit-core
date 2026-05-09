<?php

declare(strict_types=1);

namespace XrechnungKit\Notification;

use Override;

/**
 * Fans out one notification to every registered channel. An empty dispatcher
 * is a valid no-op and is the default the pipeline uses when the consumer has
 * not configured any notification channels.
 */
final class ChannelDispatcher implements NotificationDispatcherInterface
{
    /** @var list<NotificationChannelInterface> */
    private array $channels;

    public function __construct(NotificationChannelInterface ...$channels)
    {
        $this->channels = array_values($channels);
    }

    public function withChannel(NotificationChannelInterface $channel): self
    {
        $clone = clone $this;
        $clone->channels[] = $channel;
        return $clone;
    }

    #[Override]
    public function dispatch(Notification $notification): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($notification);
        }
    }
}
