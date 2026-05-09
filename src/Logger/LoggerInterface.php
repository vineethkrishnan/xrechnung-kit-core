<?php

declare(strict_types=1);

namespace XrechnungKit\Logger;

/**
 * PSR-3 compatible subset used by the core pipeline.
 *
 * The full PSR-3 \Psr\Log\LoggerInterface is a superset; consumers can pass any
 * PSR-3 logger via a thin adapter (the framework adapter packages do exactly this).
 */
interface LoggerInterface
{
    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void;
}
