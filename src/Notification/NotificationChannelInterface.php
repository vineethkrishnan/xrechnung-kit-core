<?php

declare(strict_types=1);

namespace XrechnungKit\Notification;

/**
 * A delivery mechanism for notifications. Implementations should swallow
 * transient delivery errors rather than propagating them; the pipeline must
 * not crash because Slack is down.
 */
interface NotificationChannelInterface
{
    public function send(Notification $notification): void;

    /**
     * Stable identifier for routing or filtering (e.g., "slack", "email", "log").
     */
    public function name(): string;
}
