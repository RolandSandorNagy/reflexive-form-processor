<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Tests\Support;

use RuntimeException;
use Reflexive\FormProcessor\ContactRepositoryInterface;

final class InMemoryContactRepository implements ContactRepositoryInterface
{
    /**
     * @var array<int, array{id: int, first_name: string, last_name: string, email: string}>
     */
    private array $contacts = [];

    private int $nextId = 1;

    public function get(int $id): ?array
    {
        return $this->contacts[$id] ?? null;
    }

    public function create(array $data): int
    {
        $id = $this->nextId++;

        $this->contacts[$id] = [
            'id' => $id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ];

        return $id;
    }

    public function update(int $id, array $data): array
    {
        if (!isset($this->contacts[$id])) {
            throw new RuntimeException(sprintf('Contact with ID %d was not found.', $id));
        }

        $this->contacts[$id] = array_merge($this->contacts[$id], $data);

        return $this->contacts[$id];
    }

    public function delete(int $id): void
    {
        unset($this->contacts[$id]);
    }

    public function getContactByEmail(string $email): ?array
    {
        $normalizedEmail = strtolower(trim($email));

        foreach ($this->contacts as $contact) {
            if (strtolower($contact['email']) === $normalizedEmail) {
                return $contact;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{id: int, first_name: string, last_name: string, email: string}>
     */
    public function all(): array
    {
        return $this->contacts;
    }
}
