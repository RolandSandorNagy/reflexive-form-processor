<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Tests\Support;

use RuntimeException;
use Reflexive\FormProcessor\ContactFormSubmissionRepositoryInterface;

final class InMemoryContactFormSubmissionRepository implements ContactFormSubmissionRepositoryInterface
{
    /**
     * @var array<int, array{id: int, contact_id: int, field: string, service: string, message: string, timestamp: string}>
     */
    private array $submissions = [];

    private int $nextId = 1;

    public function get(int $id): ?array
    {
        return $this->submissions[$id] ?? null;
    }

    public function create(array $data): int
    {
        $id = $this->nextId++;

        $this->submissions[$id] = [
            'id' => $id,
            'contact_id' => $data['contact_id'],
            'field' => $data['field'],
            'service' => $data['service'],
            'message' => $data['message'],
            'timestamp' => $data['timestamp'],
        ];

        return $id;
    }

    public function update(int $id, array $data): array
    {
        if (!isset($this->submissions[$id])) {
            throw new RuntimeException(sprintf('Contact form submission with ID %d was not found.', $id));
        }

        $this->submissions[$id] = array_merge($this->submissions[$id], $data);

        return $this->submissions[$id];
    }

    public function delete(int $id): void
    {
        unset($this->submissions[$id]);
    }

    public function getSubmissionsByContact(int $contact_id): array
    {
        return array_values(array_filter(
            $this->submissions,
            static fn (array $submission): bool => $submission['contact_id'] === $contact_id,
        ));
    }

    /**
     * @return array<int, array{id: int, contact_id: int, field: string, service: string, message: string, timestamp: string}>
     */
    public function all(): array
    {
        return $this->submissions;
    }
}
