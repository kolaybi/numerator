<?php

namespace KolayBi\Numerator\Tests\Models;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorSequence;
use KolayBi\Numerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorSequence::class)]
class NumeratorSequenceTest extends TestCase
{
    #[Test]
    public function testNumeratorSequenceBelongsToProfile(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $this->assertModelExists($numeratorProfile);
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorProfile);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 1);

        $numeratorSequence = NumeratorSequenceFactory::new()->for($numeratorProfile, 'profile')->createOne();
        $this->assertModelExists($numeratorSequence);
        $this->assertInstanceOf(NumeratorSequence::class, $numeratorSequence);
        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);

        $this->assertEquals(1, $numeratorSequence->profile()->count());
        $this->assertInstanceOf(NumeratorProfile::class, $numeratorSequence->profile);
        $this->assertTrue($numeratorSequence->profile->is($numeratorProfile));
    }
}
