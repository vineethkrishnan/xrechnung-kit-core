<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;

/**
 * Sends notifications via email.
 *
 * The default mailer uses PHP's built-in mail() function for zero dependencies;
 * inject a callable mailer to use Symfony Mailer, PHPMailer, SwiftMailer, etc.
 */
final class EmailChannel implements NotificationChannelInterface
{
    /** @var \Closure(string $to, string $subject, string $body, array<int, string> $headers): void */
    private \Closure $mailer;

    /**
     * @param string $to Recipient address.
     * @param string $from Sender address.
     * @param string $name Channel identifier.
     * @param string $subjectPrefix Prepended to every subject line, e.g. "[xrechnung-kit] ".
     * @param (callable(string, string, string, array<int, string>): void)|null $mailer
     */
    public function __construct(
        private readonly string $to,
        private readonly string $from,
        private readonly string $name = 'email',
        private readonly string $subjectPrefix = '',
        ?callable $mailer = null
    ) {
        $this->mailer = $mailer !== null
            ? \Closure::fromCallable($mailer)
            : self::defaultMailer();
    }

    public function send(Notification $notification): void
    {
        try {
            $subject = $this->subjectPrefix . $notification->title;
            $headers = [
                'From: ' . $this->from,
                'Content-Type: text/plain; charset=UTF-8',
                'X-XrechnungKit-Severity: ' . $notification->severity->value,
            ];
            ($this->mailer)($this->to, $subject, $notification->body, $headers);
        } catch (\Throwable) {
            // best-effort delivery
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    private static function defaultMailer(): \Closure
    {
        return static function (string $to, string $subject, string $body, array $headers): void {
            \mail($to, $subject, $body, implode("\r\n", array_map(static fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $headers)));
        };
    }
}
