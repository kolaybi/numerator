<?php

namespace KolayBi\Numerator\Tests\Services;

use Carbon\Carbon;
use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Support\Str;
use KolayBi\Numerator\Exceptions\NumberWithThisFormatExistsException;
use KolayBi\Numerator\Exceptions\OutOfBoundsException;
use KolayBi\Numerator\Models\NumeratorSequence;
use KolayBi\Numerator\Services\NumeratorSequenceService;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorSequenceService::class)]
class NumeratorSequenceServiceTest extends TestCase
{
    private NumeratorSequenceService $numeratorSequenceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numeratorSequenceService = new NumeratorSequenceService();
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithADifferentNumber()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $counter = $numeratorProfile->counter;
        $formattedNumber = Formatter::format(
            format: $numeratorProfile->format,
            number: $counter + 1,
            prefix: $numeratorProfile->prefix,
        );

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $formattedNumber,
        );

        $numeratorProfile->refresh();

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($formattedNumber, $result->formatted_number);
        $this->assertSame($counter, $numeratorProfile->counter);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithNextNumber()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $counter = $numeratorProfile->counter;
        $formattedNumber = $numeratorProfile->formattedNumber;

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $formattedNumber,
        );

        $numeratorProfile->refresh();

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($formattedNumber, $result->formatted_number);
        $this->assertSame($counter + 1, $numeratorProfile->counter);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithExistingNumber()
    {
        $modelType = fake()->word();
        $modelId = strtolower(Str::ulid());

        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'model_type'       => $modelType,
                'model_id'         => $modelId,
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $this->travel(3)->days();

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: $modelType,
            modelId: $modelId,
            formattedNumber: $numeratorProfile->formattedNumber,
        );

        $numeratorProfile->refresh();

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($numeratorProfile->updated_at->toDateString(), Carbon::now()->toDateString());
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanThrowsNumberWithThisFormatExistsExceptionWhileCreating()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'model_type'       => fake()->unique()->word(),
                'model_id'         => strtolower(Str::ulid()),
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $this->expectException(NumberWithThisFormatExistsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->unique()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanThrowsOutOfBoundsExceptionForMinValueWhileCreating()
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'min' => 10,
        ]);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->unique()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: '1',
        );
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanThrowsOutOfBoundsExceptionForMaxValueWhileCreating()
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'max' => 101,
        ]);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->unique()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: '111',
        );
    }

    #[Test]
    public function testItCanDeleteANumeratorType(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $this->numeratorSequenceService->deleteNumeratorSequence($numeratorSequence);

        $this->assertSoftDeleted(NumeratorSequence::getModel()->getTable(), ['id' => $numeratorSequence->id]);
    }

    #[Test]
    public function testItCanFindNumeratorTypeSequence(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence);

        $this->assertTrue($result->is($numeratorSequence));
    }

    #[Test]
    public function testItCanFindNumeratorTypeSequenceWithLock(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence, lock: true);

        $this->assertTrue($result->is($numeratorSequence));
    }

    #[Test]
    public function testItCanFindNumeratorTypeSequenceWithId(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence->id);

        $this->assertTrue($result->is($numeratorSequence));
    }
}
