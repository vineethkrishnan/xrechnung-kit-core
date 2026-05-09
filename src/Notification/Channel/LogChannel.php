<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use XrechnungKit\Logger\LoggerInterface;
use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;
use XrechnungKit\Notification\Severity;

/**
 * Routes a notification through the configured LoggerInterface. The logger
 * method is chosen from the notification severity.
 */
final class LogChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $name = 'log'
    ) {
    }

    public function send(Notification $notification): void
    {
        $message = '[' . $notification->title . '] ' . $notification->body;
        $context = $notification->context;

        match ($notification->severity) {
            Severity::Info => $this->logger->info($message, $context),
            Severity::Warning => $this->logger->warning($message, $context),
            Severity::Error, Severity::Critical => $this->logger->error($message, $context),
        };
    }

    public function name(): string
    {
        return $this->name;
    }
}
