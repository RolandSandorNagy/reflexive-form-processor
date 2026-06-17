<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Reflexive\FormProcessor\ContactFormProcessor;
use Reflexive\FormProcessor\ContactFormSubmissionRepositoryInterface;
use Reflexive\FormProcessor\ContactRepositoryInterface;
use Reflexive\FormProcessor\Exception\ValidationException;
use Reflexive\FormProcessor\Tests\Support\FixedClock;

final class ContactFormProcessorTest extends TestCase
{
    public function testItCreatesContactAndSubmissionWhenContactDoesNotExist(): void
    {
        $contactRepository = $this->createMock(ContactRepositoryInterface::class);
        $submissionRepository = $this->createMock(ContactFormSubmissionRepositoryInterface::class);
        $clock = new FixedClock(new DateTimeImmutable('2026-06-16T10:00:00+00:00'));

        $contactRepository
            ->expects(self::once())
            ->method('getContactByEmail')
            ->with('roland@example.com')
            ->willReturn(null);

        $contactRepository
            ->expects(self::once())
            ->method('create')
            ->with([
                'first_name' => 'Roland',
                'last_name' => 'Nagy',
                'email' => 'roland@example.com',
            ])
            ->willReturn(12);

        $contactRepository
            ->expects(self::never())
            ->method('update');

        $submissionRepository
            ->expects(self::once())
            ->method('create')
            ->with([
                'contact_id' => 12,
                'field' => 'Webfejlesztés',
                'service' => 'Kapcsolatfelvételi űrlap',
                'message' => 'Szeretnék ajánlatot kérni.',
                'timestamp' => '2026-06-16T10:00:00+00:00',
            ])
            ->willReturn(100);

        $processor = new ContactFormProcessor($contactRepository, $submissionRepository, $clock);

        $processor->process([
            'first_name' => ' Roland ',
            'last_name' => 'Nagy',
            'email' => ' Roland@example.com ',
            'field' => 'Webfejlesztés',
            'service' => 'Kapcsolatfelvételi űrlap',
            'message' => 'Szeretnék ajánlatot kérni.',
        ]);
    }

    public function testItUpdatesExistingContactAndCreatesNewSubmission(): void
    {
        $contactRepository = $this->createMock(ContactRepositoryInterface::class);
        $submissionRepository = $this->createMock(ContactFormSubmissionRepositoryInterface::class);
        $clock = new FixedClock(new DateTimeImmutable('2026-06-16T10:00:00+00:00'));

        $contactRepository
            ->expects(self::once())
            ->method('getContactByEmail')
            ->with('roland@example.com')
            ->willReturn([
                'id' => 8,
                'first_name' => 'Old',
                'last_name' => 'Name',
                'email' => 'roland@example.com',
            ]);

        $contactRepository
            ->expects(self::never())
            ->method('create');

        $contactRepository
            ->expects(self::once())
            ->method('update')
            ->with(8, [
                'first_name' => 'Roland',
                'last_name' => 'Nagy',
                'email' => 'roland@example.com',
            ])
            ->willReturn([
                'id' => 8,
                'first_name' => 'Roland',
                'last_name' => 'Nagy',
                'email' => 'roland@example.com',
            ]);

        $submissionRepository
            ->expects(self::once())
            ->method('create')
            ->with([
                'contact_id' => 8,
                'field' => 'Marketing',
                'service' => 'SEO',
                'message' => 'Második üzenet.',
                'timestamp' => '2026-06-16T12:30:00+00:00',
            ])
            ->willReturn(101);

        $processor = new ContactFormProcessor($contactRepository, $submissionRepository, $clock);

        $processor->process([
            'first_name' => 'Roland',
            'last_name' => 'Nagy',
            'email' => 'roland@example.com',
            'field' => 'Marketing',
            'service' => 'SEO',
            'message' => 'Második üzenet.',
            'timestamp' => '2026-06-16 12:30:00 UTC',
        ]);
    }

    public function testItThrowsValidationExceptionForInvalidInput(): void
    {
        $contactRepository = $this->createMock(ContactRepositoryInterface::class);
        $submissionRepository = $this->createMock(ContactFormSubmissionRepositoryInterface::class);

        $contactRepository->expects(self::never())->method('getContactByEmail');
        $contactRepository->expects(self::never())->method('create');
        $contactRepository->expects(self::never())->method('update');
        $submissionRepository->expects(self::never())->method('create');

        $processor = new ContactFormProcessor($contactRepository, $submissionRepository);

        try {
            $processor->process([
                'first_name' => '',
                'last_name' => 'Nagy',
                'email' => 'not-an-email',
                'field' => 'Webfejlesztés',
                'service' => 'Kapcsolatfelvétel',
                'message' => 'Teszt üzenet',
            ]);

            self::fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('first_name', $exception->errors());
            self::assertArrayHasKey('email', $exception->errors());
        }
    }
}
