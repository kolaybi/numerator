<?php

namespace KolayBi\Numerator\Tests\Services;

use Carbon\Carbon;
use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Support\Str;
use KolayBi\Numerator\Exceptions\NumberWithThisFormatExistsException;
use KolayBi\Numerator\Exceptions\NumeratorProfileIsNotActiveException;
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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithADifferentNumber()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithNextNumber()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithExistingNumber()
    {
        $modelType = fake()->word();
        $modelId = strtolower(Str::ulid());

        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();
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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testThrowsNumberWithThisFormatExistsExceptionForAnExistingValueWhileCreating()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();
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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testThrowsOutOfBoundsExceptionForAMinValueWhileCreating()
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'min' => 10,
        ]);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->active()->createOne();

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
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testThrowsOutOfBoundsExceptionForAMaxValueWhileCreating()
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'max' => 101,
        ]);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->active()->createOne();

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->unique()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: '111',
        );
    }

    #[Test]
    public function testItCanDeleteNumeratorSequence(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $this->numeratorSequenceService->deleteNumeratorSequence($numeratorSequence);

        $this->assertSoftDeleted(NumeratorSequence::getModel()->getTable(), ['id' => $numeratorSequence->id]);
    }

    #[Test]
    public function testItCanFindNumeratorSequence(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence);

        $this->assertTrue($result->is($numeratorSequence));
    }

    #[Test]
    public function testItCanFindNumeratorSequenceWithLock(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence, lock: true);

        $this->assertTrue($result->is($numeratorSequence));
    }

    #[Test]
    public function testItCanFindNumeratorSequenceWithId(): void
    {
        $numeratorSequence = NumeratorSequenceFactory::new()->withRequired()->createOne();

        $result = $this->numeratorSequenceService->findNumeratorSequence($numeratorSequence->id);

        $this->assertTrue($result->is($numeratorSequence));
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testThrowsNumeratorProfileIsNotActiveExceptionWhenProfileIsInactive()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $this->expectException(NumeratorProfileIsNotActiveException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanCreateNumeratorSequenceWithInactiveProfileWhenSkipActiveCheckIsTrue()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
            skipActiveCheck: true,
        );

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 1);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($numeratorProfile->formattedNumber, $result->formatted_number);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItAdvancesCounterWhenCreatingSequenceWithActiveProfile()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();
        $counter = $numeratorProfile->counter;

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );

        $numeratorProfile->refresh();
        $this->assertSame($counter + 1, $numeratorProfile->counter);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItAdvancesCounterWhenCreatingSequenceWithInactiveProfileAndSkipActiveCheck()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();
        $counter = $numeratorProfile->counter;

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
            skipActiveCheck: true,
        );

        $numeratorProfile->refresh();
        $this->assertSame($counter + 1, $numeratorProfile->counter);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanReuseDeletedSequenceWhenReuseIfDeletedIsTrue()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->withReuse(true)->createOne();

        $existingSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $existingSequence->delete();

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 2);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($numeratorProfile->formattedNumber, $result->formatted_number);
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsExceptionForDeletedSequenceWhenReuseIfDeletedIsFalse()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->withReuse(false)->createOne();

        $existingSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $existingSequence->delete();

        $this->expectException(NumberWithThisFormatExistsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsExceptionForActiveSequenceRegardlessOfReuseIfDeletedSetting()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->withReuse(true)->createOne();

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $this->expectException(NumberWithThisFormatExistsException::class);

        $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );
    }

    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanReuseDeletedSequenceWithNumeratorProfileObject()
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->withReuse(true)->createOne();

        $existingSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $existingSequence->delete();

        $result = $this->numeratorSequenceService->createNumeratorSequence(
            numeratorType: $numeratorProfile->type->name,
            modelType: fake()->word(),
            modelId: strtolower(Str::ulid()),
            formattedNumber: $numeratorProfile->formattedNumber,
        );

        $this->assertDatabaseCount(NumeratorSequence::getModel()->getTable(), 2);
        $this->assertDatabaseHas(NumeratorSequence::getModel()->getTable(), $result->getAttributes());
        $this->assertSame($numeratorProfile->formattedNumber, $result->formatted_number);
    }
}
