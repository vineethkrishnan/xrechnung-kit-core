<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\PaymentMeans;
use XrechnungKit\Mapping\PaymentMeansCode;

final class PaymentMeansTest extends TestCase
{
    #[Test]
    public function it_constructs_a_sepa_credit_transfer(): void
    {
        $pm = PaymentMeans::sepaCreditTransfer(
            iban: 'DE12500105170648489890',
            bic: 'INGDDEFFXXX',
            accountName: 'Beispiel GmbH',
            paymentReference: 'RE-2026-0001',
        );

        self::assertSame(PaymentMeansCode::SEPA_CREDIT_TRANSFER, $pm->code);
        self::assertSame('DE12500105170648489890', $pm->iban);
        self::assertSame('INGDDEFFXXX', $pm->bic);
        self::assertSame('RE-2026-0001', $pm->paymentReference);
        self::assertNull($pm->mandateReference);
    }

    #[Test]
    public function it_constructs_a_sepa_direct_debit_with_mandate(): void
    {
        $pm = PaymentMeans::sepaDirectDebit(
            iban: 'DE12500105170648489890',
            mandateReference: 'MNDT-2026-001',
        );

        self::assertSame(59, $pm->code->value);
        self::assertSame('MNDT-2026-001', $pm->mandateReference);
    }

    #[Test]
    public function it_rejects_a_sepa_direct_debit_without_mandate(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/SEPA Direct Debit .* mandate/');

        PaymentMeans::sepaDirectDebit(
            iban: 'DE12500105170648489890',
            mandateReference: '   ',
        );
    }

    #[Test]
    public function it_constructs_a_cash_payment(): void
    {
        $pm = PaymentMeans::cash('RE-2026-0001');

        self::assertSame(10, $pm->code->value);
        self::assertSame('RE-2026-0001', $pm->paymentReference);
        self::assertNull($pm->iban);
    }

    #[Test]
    public function it_constructs_a_credit_card_with_optional_pan_and_holder(): void
    {
        $pm = PaymentMeans::creditCard(cardLast4: '4242', cardHolderName: 'Anna Beispiel');

        self::assertSame(54, $pm->code->value);
        self::assertSame('4242', $pm->cardLast4);
        self::assertSame('Anna Beispiel', $pm->cardHolderName);
    }

    #[Test]
    public function it_constructs_a_bank_card_with_no_card_details(): void
    {
        $pm = PaymentMeans::bankCard();

        self::assertSame(48, $pm->code->value);
        self::assertNull($pm->cardLast4);
        self::assertNull($pm->cardHolderName);
    }

    #[Test]
    public function it_constructs_a_non_sepa_bank_transfer(): void
    {
        $pm = PaymentMeans::bankTransfer(
            iban: 'CH9300762011623852957',
            bic: 'POFICHBEXXX',
        );

        self::assertSame(42, $pm->code->value);
        self::assertSame('CH9300762011623852957', $pm->iban);
    }

    /** @return list<array{0: string}> */
    public static function validIbans(): array
    {
        return [
            ['DE12500105170648489890'],   // German 22 chars
            ['CH9300762011623852957'],    // Swiss 21 chars
            ['GB82WEST12345698765432'],   // UK 22 chars
            ['MT84MALT011000012345MTLCAST001S'],  // Malta 31 chars (max)
        ];
    }

    #[Test]
    #[DataProvider('validIbans')]
    public function it_accepts_well_formed_ibans(string $iban): void
    {
        $pm = PaymentMeans::sepaCreditTransfer($iban);
        self::assertSame($iban, $pm->iban);
    }

    /** @return list<array{0: string}> */
    public static function invalidIbans(): array
    {
        return [
            [''],                                              // empty
            ['DE12 5001 0517 0648 4898 90'],                   // contains spaces
            ['12DE500105170648489890'],                        // digits before letters
            ['de12500105170648489890'],                        // lowercase
            ['DE'],                                            // too short
            ['DE00' . '11111111111111111111111111111111'],     // too long (BBAN = 32, max 30)
        ];
    }

    #[Test]
    #[DataProvider('invalidIbans')]
    public function it_rejects_malformed_ibans(string $iban): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/IBAN must be/');

        PaymentMeans::sepaCreditTransfer($iban);
    }

    /** @return list<array{0: string}> */
    public static function validBics(): array
    {
        return [
            ['INGDDEFFXXX'],   // 11 chars
            ['INGDDEFF'],      // 8 chars (no branch)
            ['POFICHBEXXX'],   // Swiss
        ];
    }

    #[Test]
    #[DataProvider('validBics')]
    public function it_accepts_well_formed_bics(string $bic): void
    {
        $pm = PaymentMeans::sepaCreditTransfer('DE12500105170648489890', $bic);
        self::assertSame($bic, $pm->bic);
    }

    /** @return list<array{0: string}> */
    public static function invalidBics(): array
    {
        return [
            ['ING'],          // too short
            ['INGDDEFFXXXX'], // 12 chars (max 11)
            ['ingddeffxxx'],  // lowercase
            ['1NGDDEFFXXX'],  // first 4 must be letters
            ['INGD12FFXXX'],  // chars 5-6 must be letters (country)
        ];
    }

    #[Test]
    #[DataProvider('invalidBics')]
    public function it_rejects_malformed_bics(string $bic): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/BIC must be/');

        PaymentMeans::sepaCreditTransfer('DE12500105170648489890', $bic);
    }
}
