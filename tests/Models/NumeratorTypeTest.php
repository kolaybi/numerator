<?php

namespace KolayBi\Numerator\Tests\Models;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Database\Eloquent\Collection;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorType::class)]
class NumeratorTypeTest extends TestCase
{
    #[Test]
    public function testMinAttribute(): void
    {
        $numeratorProfileOne = NumeratorTypeFactory::new()->makeOne([
            'min' => null,
        ]);

        $this->assertSame(0, $numeratorProfileOne->min);

        $numeratorProfileTwo = NumeratorTypeFactory::new()->makeOne([
            'min' => 100,
        ]);

        $this->assertSame(100, $numeratorProfileTwo->min);
    }

    #[Test]
    public function testMaxAttribute(): void
    {
        $numeratorProfileOne = NumeratorTypeFactory::new()->makeOne([
            'max' => null,
        ]);

        $this->assertSame(PHP_INT_MAX, $numeratorProfileOne->max);

        $numeratorProfileTwo = NumeratorTypeFactory::new()->makeOne([
            'max' => 100,
        ]);

        $this->assertSame(100, $numeratorProfileTwo->max);
    }

    #[Test]
    public function testNumeratorTypeHasManyProfiles(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();
        $this->assertModelExists($numeratorType);
        $this->assertInstanceOf(NumeratorType::class, $numeratorType);
        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();
        $this->assertModelExists($numeratorProfile);
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorProfile);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 1);

        $this->assertEquals(1, $numeratorType->profiles()->count());
        $this->assertInstanceOf(Collection::class, $numeratorType->profiles);
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorType->profiles->first());
        $this->assertTrue($numeratorType->profiles->contains($numeratorProfile));
    }
}
