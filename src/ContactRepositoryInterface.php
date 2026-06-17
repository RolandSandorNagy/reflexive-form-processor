<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor;

interface ContactRepositoryInterface
{
    /**
     * Retrieve Contact by ID.
     *
     * @param int $id Contact ID
     *
     * @return array{
     *     id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     * }|null Contact data or null if not found
     */
    public function get(int $id): ?array;

    /**
     * Create new Contact.
     *
     * @param array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     * } $data Contact data
     *
     * @return int Created Contact ID
     */
    public function create(array $data): int;

    /**
     * Update existing Contact.
     *
     * @param int $id Contact ID
     * @param array{
     *     first_name?: string,
     *     last_name?: string,
     *     email?: string,
     * } $data Contact data
     *
     * @return array{
     *     id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     * } Updated Contact data
     */
    public function update(int $id, array $data): array;

    /**
     * Delete Contact.
     *
     * @param int $id Contact ID
     *
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Retrieve Contact by Email address.
     *
     * @param string $email Email address
     *
     * @return array{
     *     id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     * }|null Contact data or null if not found
     */
    public function getContactByEmail(string $email): ?array;
}
