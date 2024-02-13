<?php

namespace KolayBi\Numerator\Tests\Models;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Database\Eloquent\Collection;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorSequence;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorProfile::class)]
class NumeratorProfileTest extends TestCase
{
    #[Test]
    public function testFormattedNumberAttribute(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()
            ->withRequired()
            ->withFormat([NumeratorFormatVariable::LONG_YEAR, NumeratorFormatVariable::LONG_MONTH])
            ->makeOne();

        $this->assertSame(
            Formatter::format(
                format: $numeratorProfile->format,
                number: $numeratorProfile->counter,
                prefix: $numeratorProfile->prefix,
                suffix: $numeratorProfile->suffix,
                padLength: $numeratorProfile->pad_length,
            ),
            $numeratorProfile->formattedNumber,
        );
    }

    #[Test]
    public function testNumeratorProfileHasManySequences(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $this->assertModelExists($numeratorProfile);
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorProfile);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 1);

        $numeratorSequence = NumeratorSequenceFactory::new()->for($numeratorProfile, 'profile')->createOne();
        $this->assertModelExists($numeratorSequence);
        $this->assertInstanceOf(NumeratorSequence::class, $numeratorSequence);
        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);

        $this->assertEquals(1, $numeratorProfile->sequences()->count());
        $this->assertInstanceOf(Collection::class, $numeratorProfile->sequences);
        $this->assertInstanceOf(NumeratorSequence::class, $numeratorProfile->sequences->first());
        $this->assertTrue($numeratorProfile->sequences->contains($numeratorSequence));
    }

    #[Test]
    public function testNumeratorProfileHasOneType(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();
        $this->assertModelExists($numeratorType);
        $this->assertInstanceOf(NumeratorType::class, $numeratorType);
        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();
        $this->assertModelExists($numeratorProfile);
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorProfile);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 1);

        $this->assertEquals(1, $numeratorProfile->type()->count());
        $this->assertInstanceOf(NumeratorType::class, $numeratorProfile->type);
        $this->assertTrue($numeratorProfile->type->is($numeratorType));
    }
}
