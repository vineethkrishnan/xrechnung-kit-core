<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use Override;
use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;

final class NullChannel implements NotificationChannelInterface
{
    #[Override]
    public function send(Notification $notification): void
    {
    }

    #[Override]
    public function name(): string
    {
        return 'null';
    }
}
