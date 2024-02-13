<?php

namespace KolayBi\Numerator\Tests\Utils;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorTypeFactory;
use Generator;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Formatter::class)]
class FormatterTest extends TestCase
{
    private NumeratorType $numeratorType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numeratorType = NumeratorTypeFactory::new()->createOne();
    }

    #[Test]
    #[DataProvider('provideDataForTestFormat')]
    public function testFormat(?string $prefix, ?string $suffix, ?string $format, ?int $padLength, int $number): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()
            ->withType($this->numeratorType)
            ->createOne([
                'prefix'     => $prefix,
                'suffix'     => $suffix,
                'format'     => $format,
                'pad_length' => $padLength,
                'counter'    => $number,
            ]);

        $this->assertSame($numeratorProfile->formattedNumber, Formatter::format($format, $number, $prefix, $suffix, $padLength));
    }

    public static function provideDataForTestFormat(): Generator
    {
        yield '100' => ['AA', null, '%NUMBER%YYYY', null, 100];
        yield '200' => [null, null, '%YY%NUMBER', null, 200];
        yield '300' => ['BB', null, '%NUMBER%MM', null, 300];
        yield '400' => [null, null, '%M%NUMBER', null, 400];
        yield '500' => ['CC', null, '%NUMBER%DD', null, 500];
        yield '600' => [null, null, '%D%NUMBER', null, 600];
        yield '700' => ['DD', null, '%NUMBER', null, 700];
        yield '800' => [null, null, '%NUMBER', null, 800];
        yield '900' => ['AA', 'AA', '%NUMBER%YYYY', null, 900];
        yield '1000' => [null, 'BB', '%YY%NUMBER', null, 1000];
        yield '1100' => ['BB', 'CC', '%NUMBER%MM', 2, 1100];
        yield '1200' => [null, 'DD', '%M%NUMBER', 5, 1200];
        yield '1300' => ['CC', 'EE', '%NUMBER%DD', null, 1300];
        yield '1400' => [null, 'FF', '%D%NUMBER', 4, 1400];
        yield '1500' => ['DD', null, '%NUMBER', 8, 1500];
        yield '1600' => [null, null, '%NUMBER', 9, 1600];
        yield '1700' => ['AA', 'BB', '%NUMBER%YYYY', 3, 1700];
        yield '1800' => [null, 'CC', '%YY%NUMBER', 6, 1800];
        yield '1900' => ['BB', 'DD', '%NUMBER%MM', null, 1900];
        yield '2000' => [null, 'EE', '%M%NUMBER', 7, 2000];
        yield '2100' => ['CC', 'FF', '%NUMBER%DD', 8, 2100];
        yield '2200' => [null, 'GG', '%D%NUMBER', null, 2200];
        yield '2300' => ['DD', 'HH', '%NUMBER', 10, 2300];
        yield '2400' => [null, 'II', '%NUMBER', null, 2400];
        yield '2500' => ['AA', 'JJ', '%NUMBER%YYYY', 5, 2500];
        yield '2600' => [null, 'KK', '%YY%NUMBER', null, 2600];
        yield '2700' => ['BB', 'LL', '%NUMBER%MM', 3, 2700];
        yield '2800' => [null, 'MM', '%M%NUMBER', 6, 2800];
        yield '2900' => ['CC', 'NN', '%NUMBER%DD', null, 2900];
        yield '3000' => [null, 'OO', '%D%NUMBER', 8, 3000];
        yield '3100' => ['DD', 'PP', '%NUMBER', null, 3100];
        yield '3200' => [null, 'QQ', '%NUMBER', 7, 3200];
    }
}
