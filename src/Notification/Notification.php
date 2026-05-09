<?php

declare(strict_types=1);

namespace XrechnungKit\Notification;

/**
 * Operator-facing notification dispatched when the pipeline detects a condition
 * that needs human attention (typically: invalid generated XML).
 */
final class Notification
{
    /**
     * @param array<string, mixed> $context Free-form structured data the channel may include in its payload.
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly Severity $severity = Severity::Warning,
        public readonly array $context = [],
    ) {
    }

    /**
     * Stable signature used by deduplicating decorators. Same title + sorted
     * context-keys + body produce the same signature.
     */
    public function signature(): string
    {
        $contextKeys = array_keys($this->context);
        sort($contextKeys);
        return md5($this->title . '|' . implode(',', $contextKeys) . '|' . $this->body);
    }
}
