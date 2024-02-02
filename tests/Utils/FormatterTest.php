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
    public function testFormat(?string $prefix, ?string $format, int $number): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()
            ->withType($this->numeratorType)
            ->createOne([
                'prefix'  => $prefix,
                'format'  => $format,
                'counter' => $number,
            ]);

        $this->assertSame($numeratorProfile->formattedNumber, Formatter::format($format, $number, $prefix));
    }

    public static function provideDataForTestFormat(): Generator
    {
        yield '100' => ['AA', '%NUMBER%YYYY', 100];
        yield '200' => [null, '%YY%NUMBER', 200];
        yield '300' => ['BB', '%NUMBER%MM', 300];
        yield '400' => [null, '%M%NUMBER', 400];
        yield '500' => ['CC', '%NUMBER%DD', 500];
        yield '600' => [null, '%D%NUMBER', 600];
        yield '700' => ['DD', '%NUMBER', 700];
        yield '800' => [null, '%NUMBER', 800];
    }
}
