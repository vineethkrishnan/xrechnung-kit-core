<?php

declare(strict_types=1);

namespace XrechnungKit\Logger;

final class NullLogger implements LoggerInterface
{
    /** @param array<string, mixed> $context */
    #[\Override]
    public function info(string $message, array $context = []): void
    {
    }

    /** @param array<string, mixed> $context */
    #[\Override]
    public function warning(string $message, array $context = []): void
    {
    }

    /** @param array<string, mixed> $context */
    #[\Override]
    public function error(string $message, array $context = []): void
    {
    }
}
