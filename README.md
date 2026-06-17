# Reflexive Communications PHP próbamunka

Ez a megoldás a Reflexive Communications PHP / webfejlesztő próbamunka specifikációjára készült.

A projekt egy natív PHP 8.2+ service osztályt tartalmaz, amely:

- validálja és normalizálja a kapcsolatfelvételi űrlap adatait,
- email cím alapján azonosítja a kontaktot,
- új kontaktot hoz létre, ha még nem létezik,
- meglévő kontakt esetén frissíti a kontaktadatokat,
- minden érvényes űrlapbeküldésből új submission rekordot készít,
- repository interfészeken keresztül dolgozik, dependency injection használatával.

A repository implementáció nem része a feladatnak, ezért a `src` mappa csak az interfészeket és a service logikát tartalmazza. A tesztekhez egyszerű in-memory repository implementációk találhatók a `tests/Support` mappában.

## Követelmények

- PHP 8.2+
- Composer
- PHPUnit 11.5, composer dev dependency-ként

## Telepítés

```bash
composer install
```

## Tesztek futtatása

```bash
composer test
```

vagy közvetlenül:

```bash
./vendor/bin/phpunit
```

## Projektstruktúra

```text
src/
├── Clock/
│   ├── ClockInterface.php
│   └── SystemClock.php
├── Exception/
│   └── ValidationException.php
├── ContactFormProcessor.php
├── ContactFormSubmissionRepositoryInterface.php
├── ContactRepositoryInterface.php
└── FormProcessorInterface.php

tests/
├── Integration/
│   └── ContactFormProcessorIntegrationTest.php
├── Support/
│   ├── FixedClock.php
│   ├── InMemoryContactFormSubmissionRepository.php
│   └── InMemoryContactRepository.php
└── Unit/
    └── ContactFormProcessorTest.php
```

## Validációs szabályok

A következő mezők kötelezők és nem lehetnek üresek:

- `first_name`
- `last_name`
- `email`
- `field`
- `service`
- `message`

Az `email` mező érvényes email formátumot vár. Az email cím normalizálva, kisbetűsítve kerül feldolgozásra, mert a kontaktokat email alapján kell egyedileg azonosítani.

A `timestamp` mező opcionális. Ha nincs megadva, vagy üres értéket kap, a feldolgozás aktuális ideje kerül használatra. Ha meg van adva, érvényes date/time stringnek kell lennie. A mentésre átadott timestamp ISO 8601 / `DATE_ATOM` formátumra normalizálódik.

## Hibakezelés

Érvénytelen bemenet esetén a service `ValidationException` kivételt dob. A kivétel `errors()` metódusa mezőnként adja vissza a validációs hibákat.

Példa:

```php
try {
    $processor->process($data);
} catch (ValidationException $exception) {
    $errors = $exception->errors();
}
```

## Használati példa

```php
use Reflexive\FormProcessor\ContactFormProcessor;

$processor = new ContactFormProcessor(
    $contactRepository,
    $contactFormSubmissionRepository,
);

$processor->process([
    'first_name' => 'Roland',
    'last_name' => 'Nagy',
    'email' => 'roland@example.com',
    'field' => 'Webfejlesztés',
    'service' => 'Weboldal készítés',
    'message' => 'Szeretnék ajánlatot kérni.',
]);
```

## Tervezési megjegyzések

- A `ContactFormProcessor` csak az üzleti logikáért felelős.
- Az adatbázis-műveleteket repository interfészek mögé tettem.
- A repository-k konstruktoron keresztül kerülnek átadásra, így a service könnyen tesztelhető.
- A pontos idő lekérését `ClockInterface` mögé tettem, hogy a timestamp fallback determinisztikusan tesztelhető legyen.
- A megoldás nem tartalmaz framework-specifikus kódot, így később könnyen beilleszthető Laravel, Symfony vagy más PHP alkalmazásba is.
