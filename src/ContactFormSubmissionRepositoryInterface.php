<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor;

interface ContactFormSubmissionRepositoryInterface
{
    /**
     * Retrieve Contact form submission by ID.
     *
     * @param int $id Contact form submission ID
     *
     * @return array{
     *     id: int,
     *     contact_id: int,
     *     field: string,
     *     service: string,
     *     message: string,
     *     timestamp: string,
     * }|null Contact form submission data or null if not found
     */
    public function get(int $id): ?array;

    /**
     * Create new Contact form submission.
     *
     * @param array{
     *     contact_id: int,
     *     field: string,
     *     service: string,
     *     message: string,
     *     timestamp: string,
     * } $data Contact form submission data
     *
     * @return int Created Contact form submission ID
     */
    public function create(array $data): int;

    /**
     * Update existing Contact form submission.
     *
     * @param int $id Contact form submission ID
     * @param array{
     *     contact_id: int,
     *     field?: string,
     *     service?: string,
     *     message?: string,
     *     timestamp?: string,
     * } $data Contact form submission data
     *
     * @return array{
     *     id: int,
     *     contact_id: int,
     *     field: string,
     *     service: string,
     *     message: string,
     *     timestamp: string,
     * } Updated Contact form submission data
     */
    public function update(int $id, array $data): array;

    /**
     * Delete Contact form submission.
     *
     * @param int $id Contact form submission ID
     *
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Retrieve Contact form submissions of a Contact.
     *
     * @param int $contact_id Contact ID
     *
     * @return list<array{
     *     id: int,
     *     contact_id: int,
     *     field: string,
     *     service: string,
     *     message: string,
     *     timestamp: string,
     * }> Contact form submissions
     */
    public function getSubmissionsByContact(int $contact_id): array;
}
