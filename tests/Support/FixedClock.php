<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Tests\Support;

use DateTimeImmutable;
use Reflexive\FormProcessor\Clock\ClockInterface;

final class FixedClock implements ClockInterface
{
    public function __construct(
        private readonly DateTimeImmutable $fixedTime,
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return $this->fixedTime;
    }
}
