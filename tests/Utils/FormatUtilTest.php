<?php

namespace KolayBi\Numerator\Tests\Utils;

use Generator;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\FormatUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FormatUtil::class)]
class FormatUtilTest extends TestCase
{
    #[Test]
    #[DataProvider('provideDataForTestIsValidFormat')]
    public function testIsValidFormat(bool $expected, ?string $format, bool $nullable = false): void
    {
        $this->assertSame($expected, FormatUtil::isValidFormat($format, $nullable));
    }

    #[Test]
    public function testGenerateRandomFormat(): void
    {
        $randomFormat = FormatUtil::generateRandomFormat();

        if (!is_null($randomFormat)) {
            $this->assertStringContainsString('%NUMBER', $randomFormat);
        } else {
            $this->assertNull($randomFormat);
        }
    }

    #[Test]
    #[DataProvider('provideDataForTestSerializeFormat')]
    public function testSerializeFormat(?string $expected, ?array $formats, bool $exceptNumberFormat = false): void
    {
        $this->assertSame($expected, FormatUtil::serializeFormat($formats, $exceptNumberFormat));
    }

    public static function provideDataForTestIsValidFormat(): Generator
    {
        yield 'Case-1' => [false, null];
        yield 'Case-2' => [true, null, true];
        yield 'Case-3' => [false, '%YYYY%MM%DD'];
        yield 'Case-4' => [true, '%NUMBER'];
        yield 'Case-5' => [false, '%YY-AA'];
        yield 'Case-6' => [true, '%NUMBER-AA'];
        yield 'Case-7' => [false, '%YYYY', true];
        yield 'Case-8' => [true, '%MM%NUMBER%DD', true];
    }

    public static function provideDataForTestSerializeFormat(): Generator
    {
        yield 'Case-1' => [null, null];
        yield 'Case-2' => [null, []];
        yield 'Case-3' => ['%NUMBER', ['%NUMBER']];
        yield 'Case-4' => ['%NUMBER', [NumeratorFormatVariable::NUMBER]];
        yield 'Case-5' => ['%NUMBER%YY', ['%NUMBER', '%YY'], true];
        yield 'Case-6' => ['%NUMBER%YY', [NumeratorFormatVariable::NUMBER, '%YY'], true];
        yield 'Case-7' => ['%YYYY%NUMBER', ['%YYYY']];
        yield 'Case-8' => ['%YYYY', ['%YYYY'], true];
    }
}
