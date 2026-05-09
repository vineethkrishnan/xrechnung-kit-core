<?php

declare(strict_types=1);

namespace XrechnungKit\Notification;

/**
 * Decorator that drops repeated notifications with the same signature within a
 * configurable TTL window. Cache is in-process; horizontally-scaled deployments
 * should swap this for a Redis-backed equivalent (adapter packages may provide one).
 */
final class DeduplicatingDispatcher implements NotificationDispatcherInterface
{
    /** @var array<string, int> signature -> unix timestamp of last emission */
    private array $lastEmitted = [];

    /**
     * @param (\Closure(): int)|null $clock Injectable now() for deterministic tests; defaults to time().
     */
    public function __construct(
        private readonly NotificationDispatcherInterface $inner,
        private readonly int $ttlSeconds = 1800,
        private readonly ?\Closure $clock = null
    ) {
    }

    #[\Override]
    public function dispatch(Notification $notification): void
    {
        $now = $this->now();
        $signature = $notification->signature();
        $last = $this->lastEmitted[$signature] ?? null;

        if ($last !== null && ($now - $last) < $this->ttlSeconds) {
            return;
        }

        $this->lastEmitted[$signature] = $now;
        $this->inner->dispatch($notification);
    }

    private function now(): int
    {
        return $this->clock !== null ? ($this->clock)() : time();
    }
}
