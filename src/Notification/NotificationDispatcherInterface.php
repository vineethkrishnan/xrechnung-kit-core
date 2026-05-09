<?php

declare(strict_types=1);

namespace XrechnungKit\Notification;

interface NotificationDispatcherInterface
{
    public function dispatch(Notification $notification): void;
}
