<?php

declare(strict_types=1);

namespace Reflexive\FormProcessor\Tests\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Reflexive\FormProcessor\ContactFormProcessor;
use Reflexive\FormProcessor\Tests\Support\FixedClock;
use Reflexive\FormProcessor\Tests\Support\InMemoryContactFormSubmissionRepository;
use Reflexive\FormProcessor\Tests\Support\InMemoryContactRepository;

final class ContactFormProcessorIntegrationTest extends TestCase
{
    public function testSameEmailProducesSingleUpdatedContactAndMultipleSubmissions(): void
    {
        $contactRepository = new InMemoryContactRepository();
        $submissionRepository = new InMemoryContactFormSubmissionRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-06-16T10:00:00+00:00'));

        $processor = new ContactFormProcessor($contactRepository, $submissionRepository, $clock);

        $processor->process([
            'first_name' => 'Roland',
            'last_name' => 'Nagy',
            'email' => 'Roland@example.com',
            'field' => 'Webfejlesztés',
            'service' => 'Weboldal készítés',
            'message' => 'Első üzenet.',
        ]);

        $processor->process([
            'first_name' => 'Roli',
            'last_name' => 'Nagy',
            'email' => 'roland@example.com',
            'field' => 'Marketing',
            'service' => 'SEO',
            'message' => 'Második üzenet.',
            'timestamp' => '2026-06-17T09:15:00+00:00',
        ]);

        $contacts = $contactRepository->all();

        self::assertCount(1, $contacts);
        self::assertSame(1, $contacts[1]['id']);
        self::assertSame('Roli', $contacts[1]['first_name']);
        self::assertSame('Nagy', $contacts[1]['last_name']);
        self::assertSame('roland@example.com', $contacts[1]['email']);

        $submissions = $submissionRepository->getSubmissionsByContact(1);

        self::assertCount(2, $submissions);
        self::assertSame('Első üzenet.', $submissions[0]['message']);
        self::assertSame('2026-06-16T10:00:00+00:00', $submissions[0]['timestamp']);
        self::assertSame('Második üzenet.', $submissions[1]['message']);
        self::assertSame('2026-06-17T09:15:00+00:00', $submissions[1]['timestamp']);
    }
}
