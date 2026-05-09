<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use Closure;
use Override;
use Throwable;
use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;
use XrechnungKit\Notification\Severity;

/**
 * Posts to a Slack incoming webhook URL.
 *
 * The HTTP send is injected as a callable so callers can supply their own client
 * (PSR-18, Guzzle, a stub for tests). The default implementation uses
 * stream_context_create + file_get_contents and requires no PHP extensions
 * beyond the core.
 *
 * The webhook URL is a constructor parameter; never hardcode a webhook in source.
 */
final class SlackChannel implements NotificationChannelInterface
{
    /** @var Closure(string, array<int, string>, string): void */
    private Closure $httpClient;

    /**
     * @param string $webhookUrl Slack incoming webhook URL.
     * @param string $name Channel identifier used by routing/filtering.
     * @param string|null $username Optional username override.
     * @param string|null $iconEmoji Optional emoji override (e.g. ":warning:").
     * @param (callable(string, array<int, string>, string): void)|null $httpClient
     */
    public function __construct(
        private readonly string $webhookUrl,
        private readonly string $name = 'slack',
        private readonly ?string $username = null,
        private readonly ?string $iconEmoji = null,
        ?callable $httpClient = null,
    ) {
        $this->httpClient = $httpClient !== null
            ? Closure::fromCallable($httpClient)
            : self::defaultHttpClient();
    }

    #[Override]
    public function send(Notification $notification): void
    {
        $payload = [
            'text' => \sprintf("*%s*\n%s", $notification->title, $notification->body),
        ];
        if ($this->username !== null) {
            $payload['username'] = $this->username;
        }
        $payload['icon_emoji'] = $this->iconEmoji ?? self::defaultEmojiFor($notification->severity);

        try {
            ($this->httpClient)(
                $this->webhookUrl,
                ['Content-Type: application/json'],
                json_encode($payload, JSON_THROW_ON_ERROR)
            );
        } catch (Throwable) {
            // Notification delivery is best-effort; never crash the pipeline.
        }
    }

    #[Override]
    public function name(): string
    {
        return $this->name;
    }

    private static function defaultEmojiFor(Severity $severity): string
    {
        return match ($severity) {
            Severity::Info => ':information_source:',
            Severity::Warning => ':warning:',
            Severity::Error => ':rotating_light:',
            Severity::Critical => ':fire:',
        };
    }

    private static function defaultHttpClient(): Closure
    {
        return static function (string $url, array $headers, string $body): void {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", array_map(static fn (mixed $v): string => \is_scalar($v) ? (string) $v : '', $headers)),
                    'content' => $body,
                    'timeout' => 5,
                    'ignore_errors' => true,
                    'protocol_version' => 1.1,
                ],
            ]);
            @file_get_contents($url, false, $context);
        };
    }
}
