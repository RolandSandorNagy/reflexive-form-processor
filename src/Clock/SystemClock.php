<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Clock;

use DateTimeImmutable;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
