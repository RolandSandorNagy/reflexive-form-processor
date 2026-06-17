<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor;

use DateTimeImmutable;
use Exception;
use Reflexive\FormProcessor\Clock\ClockInterface;
use Reflexive\FormProcessor\Clock\SystemClock;
use Reflexive\FormProcessor\Exception\ValidationException;

final class ContactFormProcessor implements FormProcessorInterface
{
    private ClockInterface $clock;

    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly ContactFormSubmissionRepositoryInterface $submissionRepository,
        ?ClockInterface $clock = null,
    ) {
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function process(array $data): void
    {
        $normalizedData = $this->validateAndNormalize($data);

        $contactData = [
            'first_name' => $normalizedData['first_name'],
            'last_name' => $normalizedData['last_name'],
            'email' => $normalizedData['email'],
        ];

        $existingContact = $this->contactRepository->getContactByEmail($normalizedData['email']);

        if ($existingContact === null) {
            $contactId = $this->contactRepository->create($contactData);
        } else {
            $contactId = $existingContact['id'];
            $this->contactRepository->update($contactId, $contactData);
        }

        $this->submissionRepository->create([
            'contact_id' => $contactId,
            'field' => $normalizedData['field'],
            'service' => $normalizedData['service'],
            'message' => $normalizedData['message'],
            'timestamp' => $normalizedData['timestamp'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     field: string,
     *     service: string,
     *     message: string,
     *     timestamp: string,
     * }
     */
    private function validateAndNormalize(array $data): array
    {
        $errors = [];

        $firstName = $this->getRequiredString($data, 'first_name', $errors);
        $lastName = $this->getRequiredString($data, 'last_name', $errors);
        $email = $this->getEmail($data, $errors);
        $field = $this->getRequiredString($data, 'field', $errors);
        $service = $this->getRequiredString($data, 'service', $errors);
        $message = $this->getRequiredString($data, 'message', $errors, false);
        $timestamp = $this->getTimestamp($data, $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'field' => $field,
            'service' => $service,
            'message' => $message,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $errors
     */
    private function getRequiredString(
        array $data,
        string $key,
        array &$errors,
        bool $collapseWhitespace = true,
    ): string {
        if (!array_key_exists($key, $data)) {
            $errors[$key] = sprintf('The %s field is required.', $key);
            return '';
        }

        if (!is_string($data[$key])) {
            $errors[$key] = sprintf('The %s field must be a string.', $key);
            return '';
        }

        $value = trim($data[$key]);

        if ($value === '') {
            $errors[$key] = sprintf('The %s field cannot be empty.', $key);
            return '';
        }

        if ($collapseWhitespace) {
            $value = (string) preg_replace('/\s+/', ' ', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $errors
     */
    private function getEmail(array $data, array &$errors): string
    {
        if (!array_key_exists('email', $data)) {
            $errors['email'] = 'The email field is required.';
            return '';
        }

        if (!is_string($data['email'])) {
            $errors['email'] = 'The email field must be a string.';
            return '';
        }

        $email = strtolower(trim($data['email']));

        if ($email === '') {
            $errors['email'] = 'The email field cannot be empty.';
            return '';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'The email field must contain a valid email address.';
            return '';
        }

        return $email;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $errors
     */
    private function getTimestamp(array $data, array &$errors): string
    {
        if (!array_key_exists('timestamp', $data) || $data['timestamp'] === null) {
            return $this->clock->now()->format(DATE_ATOM);
        }

        if (!is_string($data['timestamp'])) {
            $errors['timestamp'] = 'The timestamp field must be a string when provided.';
            return '';
        }

        $timestamp = trim($data['timestamp']);

        if ($timestamp === '') {
            return $this->clock->now()->format(DATE_ATOM);
        }

        try {
            return (new DateTimeImmutable($timestamp))->format(DATE_ATOM);
        } catch (Exception) {
            $errors['timestamp'] = 'The timestamp field must contain a valid date/time value.';
            return '';
        }
    }
}
