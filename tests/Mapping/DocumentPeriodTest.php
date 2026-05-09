<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\DocumentPeriod;

final class DocumentPeriodTest extends TestCase
{
    #[Test]
    public function it_accepts_a_valid_period(): void
    {
        $period = new DocumentPeriod(
            new \DateTimeImmutable('2026-05-01'),
            new \DateTimeImmutable('2026-05-31'),
        );

        self::assertSame('2026-05-01', $period->start->format('Y-m-d'));
        self::assertSame('2026-05-31', $period->end->format('Y-m-d'));
    }

    #[Test]
    public function it_accepts_a_single_day_period(): void
    {
        $sameDay = new \DateTimeImmutable('2026-05-09');
        $period = new DocumentPeriod($sameDay, $sameDay);

        self::assertSame('2026-05-09', $period->start->format('Y-m-d'));
        self::assertSame('2026-05-09', $period->end->format('Y-m-d'));
    }

    #[Test]
    public function it_rejects_a_period_where_end_precedes_start(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Document period end .* must be on or after start/');

        new DocumentPeriod(
            new \DateTimeImmutable('2026-05-31'),
            new \DateTimeImmutable('2026-05-01'),
        );
    }
}
