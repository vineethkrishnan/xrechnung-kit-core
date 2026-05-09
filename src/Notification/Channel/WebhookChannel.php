<?php

declare(strict_types=1);

namespace XrechnungKit\Notification\Channel;

use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationChannelInterface;

/**
 * Generic JSON HTTP POST. Works with Discord, Microsoft Teams, custom webhooks,
 * and most pull-style monitoring endpoints.
 *
 * The payload-shaping callable lets you adapt the Notification to whatever JSON
 * the target endpoint expects. Default payload is { title, body, severity, context }.
 *
 * The default HTTP transport uses stream_context_create + file_get_contents and
 * requires no PHP extensions beyond the core. Inject a custom $httpClient to use
 * Guzzle, Symfony HttpClient, or any PSR-18 client.
 */
final class WebhookChannel implements NotificationChannelInterface
{
    /** @var \Closure(string, array<int, string>, string): void */
    private \Closure $httpClient;

    /** @var \Closure(Notification): array<string, mixed> */
    private \Closure $payloadFactory;

    /**
     * @param string $url The webhook URL.
     * @param string $name Channel identifier used by routing/filtering.
     * @param array<int, string> $extraHeaders Headers added on every send (e.g. auth tokens).
     * @param (callable(Notification): array<string, mixed>)|null $payloadFactory Maps a Notification to the JSON body shape.
     * @param (callable(string, array<int, string>, string): void)|null $httpClient Custom HTTP transport.
     */
    public function __construct(
        private readonly string $url,
        private readonly string $name = 'webhook',
        private readonly array $extraHeaders = [],
        ?callable $payloadFactory = null,
        ?callable $httpClient = null
    ) {
        $this->payloadFactory = $payloadFactory !== null
            ? \Closure::fromCallable($payloadFactory)
            : static fn (Notification $n): array => [
                'title' => $n->title,
                'body' => $n->body,
                'severity' => $n->severity->value,
                'context' => $n->context,
            ];
        $this->httpClient = $httpClient !== null
            ? \Closure::fromCallable($httpClient)
            : self::defaultHttpClient();
    }

    public function send(Notification $notification): void
    {
        try {
            $payload = ($this->payloadFactory)($notification);
            ($this->httpClient)(
                $this->url,
                array_merge(['Content-Type: application/json'], $this->extraHeaders),
                json_encode($payload, JSON_THROW_ON_ERROR)
            );
        } catch (\Throwable) {
            // best-effort delivery
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    private static function defaultHttpClient(): \Closure
    {
        return static function (string $url, array $headers, string $body): void {
            $context = \stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", array_map(static fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $headers)),
                    'content' => $body,
                    'timeout' => 5,
                    'ignore_errors' => true,
                    'protocol_version' => 1.1,
                ],
            ]);
            @\file_get_contents($url, false, $context);
        };
    }
}
