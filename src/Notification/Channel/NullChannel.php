<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;

final class NullChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): void
    {
    }

    public function name(): string
    {
        return 'null';
    }
}
