<?php

namespace KolayBi\Numerator\Tests\Utils;

use Generator;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Formatter::class)]
class FormatterTest extends TestCase
{
    #[DataProvider('provideDataForFormat')]
    #[Test]
    public function testFormat(string $expected, ?string $prefix, ?string $suffix, ?string $format, ?int $padLength, int $number): void
    {
        $this->assertSame($expected, Formatter::format($format, $number, $prefix, $suffix, $padLength));
    }

    public static function provideDataForFormat(): Generator
    {
        yield 'Case-1' => ['AA-1001999', 'AA', null, '%NUMBER%YYYY', null, 100];
        yield 'Case-2' => ['99200', null, null, '%YY%NUMBER', null, 200];
        yield 'Case-3' => ['BB-30007', 'BB', null, '%NUMBER%MM', null, 300];
        yield 'Case-4' => ['7400', null, null, '%M%NUMBER', null, 400];
        yield 'Case-5' => ['CC-50023', 'CC', null, '%NUMBER%DD', null, 500];
        yield 'Case-6' => ['23600', null, null, '%D%NUMBER', null, 600];
        yield 'Case-7' => ['DD-700', 'DD', null, '%NUMBER', null, 700];
        yield 'Case-8' => ['800', null, null, '%NUMBER', null, 800];
        yield 'Case-9' => ['AA-9001999-AA', 'AA', 'AA', '%NUMBER%YYYY', null, 900];
        yield 'Case-10' => ['991000-BB', null, 'BB', '%YY%NUMBER', null, 1000];
        yield 'Case-11' => ['BB-110007-CC', 'BB', 'CC', '%NUMBER%MM', 2, 1100];
        yield 'Case-12' => ['701200-DD', null, 'DD', '%M%NUMBER', 5, 1200];
        yield 'Case-13' => ['CC-130023-EE', 'CC', 'EE', '%NUMBER%DD', null, 1300];
        yield 'Case-14' => ['231400-FF', null, 'FF', '%D%NUMBER', 4, 1400];
        yield 'Case-15' => ['DD-00001500', 'DD', null, '%NUMBER', 8, 1500];
        yield 'Case-16' => ['000001600', null, null, '%NUMBER', 9, 1600];
        yield 'Case-17' => ['AA-17001999-BB', 'AA', 'BB', '%NUMBER%YYYY', 3, 1700];
        yield 'Case-18' => ['99001800-CC', null, 'CC', '%YY%NUMBER', 6, 1800];
        yield 'Case-19' => ['BB-190007-DD', 'BB', 'DD', '%NUMBER%MM', null, 1900];
        yield 'Case-20' => ['70002000-EE', null, 'EE', '%M%NUMBER', 7, 2000];
        yield 'Case-21' => ['CC-0000210023-FF', 'CC', 'FF', '%NUMBER%DD', 8, 2100];
        yield 'Case-22' => ['232200-GG', null, 'GG', '%D%NUMBER', null, 2200];
        yield 'Case-23' => ['DD-0000002300-HH', 'DD', 'HH', '%NUMBER', 10, 2300];
        yield 'Case-24' => ['2400-II', null, 'II', '%NUMBER', null, 2400];
        yield 'Case-25' => ['AA-025001999-JJ', 'AA', 'JJ', '%NUMBER%YYYY', 5, 2500];
        yield 'Case-26' => ['992600-KK', null, 'KK', '%YY%NUMBER', null, 2600];
        yield 'Case-27' => ['BB-270007-LL', 'BB', 'LL', '%NUMBER%MM', 3, 2700];
        yield 'Case-28' => ['7002800-MM', null, 'MM', '%M%NUMBER', 6, 2800];
        yield 'Case-29' => ['CC-290023-NN', 'CC', 'NN', '%NUMBER%DD', null, 2900];
        yield 'Case-30' => ['2300003000-OO', null, 'OO', '%D%NUMBER', 8, 3000];
        yield 'Case-31' => ['DD-3100-PP', 'DD', 'PP', '%NUMBER', null, 3100];
        yield 'Case-32' => ['0003200-QQ', null, 'QQ', '%NUMBER', 7, 3200];
    }
}
