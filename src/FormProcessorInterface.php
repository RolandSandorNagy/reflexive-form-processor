<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor;

interface FormProcessorInterface
{
    /**
     * Process form submission data.
     * Validate, and persist the data as needed.
     *
     * @param array<string, mixed> $data Submitted data
     *
     * @return void
     */
    public function process(array $data): void;
}
