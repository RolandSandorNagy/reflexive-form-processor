<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
