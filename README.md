# xrechnung-kit-core

> EN 16931 / XRechnung 3.0 compliant e-invoice generator and validator for PHP. Framework-agnostic core.

[![Packagist](https://img.shields.io/packagist/v/vineethkrishnan/xrechnung-kit-core.svg)](https://packagist.org/packages/vineethkrishnan/xrechnung-kit-core)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/vineethkrishnan/xrechnung-kit/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892bf.svg)](https://www.php.net/supported-versions)

This is the framework-agnostic core of [xrechnung-kit](https://github.com/vineethkrishnan/xrechnung-kit). It turns a typed PHP value object describing an invoice into a KoSIT-strict valid XRechnung 3.0 / EN 16931 XML document, validates the output in memory before writing to disk, and quarantines invalid output.

This package is intentionally minimal. It has no framework dependencies and no runtime network calls. Framework adapters (Laravel, Symfony, CakePHP, Laminas) and the optional KoSIT Schematron bundle are published as separate packages from the same monorepo.

## Installation

```bash
composer require vineethkrishnan/xrechnung-kit-core
```

Optional KoSIT Schematron validation:

```bash
composer require --dev vineethkrishnan/xrechnung-kit-kosit-bundle
```

Framework adapters:

```bash
composer require vineethkrishnan/xrechnung-kit-laravel
composer require vineethkrishnan/xrechnung-kit-symfony
composer require vineethkrishnan/xrechnung-kit-cakephp
composer require vineethkrishnan/xrechnung-kit-laminas
```

## Quick start

```php
use XrechnungKit\Builder\XRechnungBuilder;
use XrechnungKit\Generator\XRechnungGenerator;
use XrechnungKit\Validator\XRechnungValidator;

$mappingData = (new MyInvoiceMapper($invoice, $customer))->produce();

$entity = XRechnungBuilder::buildEntity($mappingData);
$generator = new XRechnungGenerator($entity);
$path = $generator->generateXRechnung('/path/to/Invoice_RE-1.xml');

$validator = new XRechnungValidator();
$ok = $validator->validate($path);
```

The generator runs UBL XSD validation in memory before writing. Invalid output lands at `*_invalid.xml` and triggers a deduplicated operator alert.

Document classes supported:

- Standard invoice (UNTDID 380)
- Partial invoice / deposit / Anzahlung (UNTDID 326)
- Caution / security deposit
- Credit note / cancellation (UNTDID 381)
- Deposit cancellation

## Requirements

- PHP 8.1, 8.2, 8.3, or 8.4
- `ext-libxml`, `ext-dom`, `ext-mbstring`
- Optional `psr/log`
- Java 11+ for KoSIT Schematron validation (only when running `validate-kosit`)

## Repository

Source, issues, docs, and PRs live in the monorepo: [vineethkrishnan/xrechnung-kit](https://github.com/vineethkrishnan/xrechnung-kit).

This repo (`xrechnung-kit-core`) is an auto-generated split of the monorepo's `core/` subtree, published for Packagist. **Do not open PRs here**; open them against the monorepo.

- Documentation: [docs/](https://github.com/vineethkrishnan/xrechnung-kit/tree/main/docs)
- Issues: [github.com/vineethkrishnan/xrechnung-kit/issues](https://github.com/vineethkrishnan/xrechnung-kit/issues)
- Contributing: [CONTRIBUTING.md](https://github.com/vineethkrishnan/xrechnung-kit/blob/main/CONTRIBUTING.md)
- Security disclosure: [SECURITY.md](https://github.com/vineethkrishnan/xrechnung-kit/blob/main/SECURITY.md)

## License

[MIT](https://github.com/vineethkrishnan/xrechnung-kit/blob/main/LICENSE). Bundled UBL XSDs and KoSIT scenarios retain their original licenses; see [LICENSE-third-party.md](https://github.com/vineethkrishnan/xrechnung-kit/blob/main/LICENSE-third-party.md).

## Trademark notice

"XRechnung" is a German federal e-invoicing standard maintained by KoSIT (Koordinierungsstelle fuer IT-Standards). xrechnung-kit is an independent open source library and is neither affiliated with nor endorsed by KoSIT or any German government agency.
