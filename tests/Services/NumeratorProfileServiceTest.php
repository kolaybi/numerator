<?php

namespace KolayBi\Numerator\Tests\Services;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
use KolayBi\Numerator\Exceptions\InvalidFormatException;
use KolayBi\Numerator\Exceptions\OutOfBoundsException;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorSequence;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Services\NumeratorProfileService;
use KolayBi\Numerator\Tests\TestCase;
use KolayBi\Numerator\Utils\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorProfileService::class)]
class NumeratorProfileServiceTest extends TestCase
{
    private NumeratorProfileService $numeratorProfileService;
    private string $tenantIdColumn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantIdColumn = Config::get('numerator.database.tenant_id_column');
        $this->numeratorProfileService = new NumeratorProfileService();
    }

    #[Test]
    public function testItCanGetNumeratorProfiles()
    {
        $numeratorProfiles = NumeratorProfileFactory::times(10)->withRequired()->create();

        $result = $this->numeratorProfileService->getNumeratorProfiles();

        $this->assertCount(10, $result->toArray());
        foreach ($numeratorProfiles as $numeratorProfile) {
            $this->assertTrue($result->contains($numeratorProfile));
        }
    }

    #[Test]
    public function testItCanGetANumeratorProfile(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->getNumeratorProfile($numeratorProfile->id);

        $this->assertTrue($result->is($numeratorProfile));
    }

    /**
     * @throws InvalidFormatException
     */
    #[Test]
    public function testItCanCreateNumeratorProfile(): void
    {
        $numeratorProfileData = NumeratorProfileFactory::new()->withRequired()->makeOne()->getAttributes();

        $this->numeratorProfileService->createNumeratorProfile(
            tenantId: $numeratorProfileData[$this->tenantIdColumn],
            data: $numeratorProfileData,
        );

        $this->assertDatabaseHas(NumeratorProfile::getModel()->getTable(), $numeratorProfileData);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 1);
    }

    /**
     * @throws InvalidFormatException
     */
    #[Test]
    public function testItThrowsInvalidFormatExceptionWhileCreating(): void
    {
        $numeratorProfileData = NumeratorProfileFactory::new()
            ->withRequired()
            ->withFormat([NumeratorFormatVariable::LONG_YEAR], includeNumberFormat: false)
            ->makeOne()
            ->getAttributes();

        $this->expectException(InvalidFormatException::class);

        $this->numeratorProfileService->createNumeratorProfile(
            tenantId: $numeratorProfileData[$this->tenantIdColumn],
            data: $numeratorProfileData,
        );
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanUpdateNumeratorProfileExceptForStartValue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $data = [
            'prefix' => Str::random(3),
        ];

        $updatedNumeratorProfile = $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);

        $this->assertTrue($updatedNumeratorProfile->is($numeratorProfile));
        $this->assertSame($updatedNumeratorProfile->prefix, $data['prefix']);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanUpdateStartValueOfTheNumeratorProfile(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $data = [
            'start' => $numeratorProfile->start + 1,
        ];

        $updatedNumeratorProfile = $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);

        $this->assertTrue($updatedNumeratorProfile->is($numeratorProfile));
        $this->assertSame($updatedNumeratorProfile->start, $data['start']);
        $this->assertSame($updatedNumeratorProfile->counter, $data['start']);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanUpdateStartValueOfTheNumeratorProfileAndAdvanceCounter(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => Formatter::format(
                    format: $numeratorProfile->format,
                    number: $numeratorProfile->start + 1,
                    prefix: $numeratorProfile->prefix,
                    suffix: $numeratorProfile->suffix,
                    padLength: $numeratorProfile->pad_length,
                ),
            ]);

        $data = [
            'start' => $numeratorProfile->start + 1,
        ];

        $updatedNumeratorProfile = $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);

        $this->assertTrue($updatedNumeratorProfile->is($numeratorProfile));
        $this->assertSame($updatedNumeratorProfile->start, $data['start']);
        $this->assertSame($updatedNumeratorProfile->counter, $numeratorProfile->start + 2);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsOutOfBoundsExceptionForInvalidMinValueWhileUpdating(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'min' => 10,
        ]);
        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $data = [
            'start' => $numeratorProfile->type->min - 1,
        ];

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsOutOfBoundsExceptionForInvalidMaxValueWhileUpdating(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'max' => 10,
        ]);
        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $data = [
            'start' => $numeratorProfile->type->max + 1,
        ];

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsInvalidFormatExceptionWhileUpdating(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $data = [
            'format' => NumeratorFormatVariable::LONG_YEAR->value,
        ];

        $this->expectException(InvalidFormatException::class);

        $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);
    }

    #[Test]
    public function testItCanDeleteANumeratorProfile(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $numeratorSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne();

        $this->numeratorProfileService->deleteNumeratorProfile($numeratorProfile);

        $this->assertSoftDeleted(NumeratorProfile::getModel()->getTable(), ['id' => $numeratorProfile->id]);
        $this->assertSoftDeleted(NumeratorSequence::getModel()->getTable(), ['id' => $numeratorSequence->id]);
    }

    /**
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanAdvanceCounter(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $counter = $numeratorProfile->counter;

        $this->numeratorProfileService->advanceCounter($numeratorProfile, number: $counter + 1);

        $this->assertSame($counter + 1, $numeratorProfile->refresh()->counter);
    }

    /**
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItThrowsOutOfBoundsExceptionForExceedingValuesWhileAdvancingCounter(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'max' => 10,
        ]);
        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorProfileService->advanceCounter($numeratorProfile, number: $numeratorType->max + 1);
    }

    /**
     * @throws InvalidFormatException
     */
    #[Test]
    public function testItCanAttachExistingTenants(): void
    {
        NumeratorProfileFactory::times(3)->withRequired()->create();

        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $this->numeratorProfileService->attachExistingTenants([
            'type_id' => $numeratorType->id,
            'format'  => $numeratorType->format,
            'start'   => $numeratorType->min,
            'counter' => $numeratorType->min,
        ]);

        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1 + 1);
        $this->assertCount(3, $numeratorType->profiles->toArray());
    }

    /**
     * @throws InvalidFormatException
     */
    #[Test]
    public function testItThrowsInvalidFormatExceptionWhileAttachExistingTenants(): void
    {
        NumeratorProfileFactory::times(3)->withRequired()->create();

        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $this->expectException(InvalidFormatException::class);

        $this->numeratorProfileService->attachExistingTenants([
            'type_id' => $numeratorType->id,
            'format'  => NumeratorFormatVariable::LONG_YEAR->value,
            'start'   => $numeratorType->min,
            'counter' => $numeratorType->min,
        ]);
    }

    #[Test]
    public function testItCanFindNumeratorProfile(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfile($numeratorProfile);

        $this->assertTrue($result->is($numeratorProfile));
    }

    #[Test]
    public function testItCanFindNumeratorProfileWithLock(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfile($numeratorProfile, lock: true);

        $this->assertTrue($result->is($numeratorProfile));
    }

    #[Test]
    public function testItCanFindNumeratorProfileWithId(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfile($numeratorProfile->id);

        $this->assertTrue($result->is($numeratorProfile));
    }

    #[Test]
    public function testItCanFindNumeratorTypeByName(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfileByType($numeratorProfile->type->name);

        $this->assertTrue($result->is($numeratorProfile));
    }

    #[Test]
    public function testItCanGetCounter(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->getCounter($numeratorProfile->type->name);

        $this->assertTrue($result->is($numeratorProfile));
    }

    #[Test]
    public function testHasSequenceReturnsTrueWhenASequenceExists(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $hasSequence = $this->numeratorProfileService->hasSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertTrue($hasSequence);
    }

    #[Test]
    public function testHasSequenceReturnsFalseWhenASequenceDoesNotExist(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $hasSequence = $this->numeratorProfileService->hasSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertFalse($hasSequence);
    }

    #[Test]
    public function testHasSequenceReturnsTrueWhenASequenceExistsWhileExcludingAModelId(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
                'model_id'         => strtolower(Str::ulid()),
            ]);

        $hasSequence = $this->numeratorProfileService->hasSequence(
            profile: $numeratorProfile,
            formattedNumber: $numeratorProfile->formattedNumber,
            excludedModelId: strtolower(Str::ulid()),
        );

        $this->assertTrue($hasSequence);
    }

    #[Test]
    public function testHasSequenceReturnsFalseWhenASequenceDoesNotExistWhileExcludingAModelId(): void
    {
        $modelId = strtolower(Str::ulid());

        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
                'model_id'         => $modelId,
            ]);

        $hasSequence = $this->numeratorProfileService->hasSequence(
            profile: $numeratorProfile,
            formattedNumber: $numeratorProfile->formattedNumber,
            excludedModelId: $modelId,
        );

        $this->assertFalse($hasSequence);
    }

    #[Test]
    public function testItCanGetNumeratorProfilesWithOnlyActiveTrue()
    {
        NumeratorProfileFactory::times(3)->withRequired()->active()->create();
        NumeratorProfileFactory::times(2)->withRequired()->active(false)->create();

        $result = $this->numeratorProfileService->getNumeratorProfiles(onlyActive: true);

        $this->assertCount(3, $result->toArray());
        foreach ($result as $profile) {
            $this->assertTrue($profile->is_active);
        }
    }

    #[Test]
    public function testItCanGetNumeratorProfilesWithOnlyActiveFalse()
    {
        NumeratorProfileFactory::times(3)->withRequired()->active()->create();
        NumeratorProfileFactory::times(2)->withRequired()->active(false)->create();

        $result = $this->numeratorProfileService->getNumeratorProfiles(onlyActive: false);

        $this->assertCount(5, $result->toArray());
    }

    #[Test]
    public function testItCanGetNumeratorProfileWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

        $result = $this->numeratorProfileService->getNumeratorProfile($numeratorProfile->id, onlyActive: true);

        $this->assertTrue($result->is($numeratorProfile));
        $this->assertTrue($result->is_active);
    }

    #[Test]
    public function testItCannotGetInactiveNumeratorProfileWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $this->expectException(ModelNotFoundException::class);

        $this->numeratorProfileService->getNumeratorProfile($numeratorProfile->id, onlyActive: true);
    }

    #[Test]
    public function testItCanFindNumeratorProfileWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfile($numeratorProfile, onlyActive: true);

        $this->assertTrue($result->is($numeratorProfile));
        $this->assertTrue($result->is_active);
    }

    #[Test]
    public function testItCannotFindInactiveNumeratorProfileWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $this->expectException(ModelNotFoundException::class);

        $this->numeratorProfileService->findNumeratorProfile($numeratorProfile, onlyActive: true);
    }

    #[Test]
    public function testItCanFindNumeratorProfileByTypeWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

        $result = $this->numeratorProfileService->findNumeratorProfileByType($numeratorProfile->type->name, onlyActive: true);

        $this->assertTrue($result->is($numeratorProfile));
        $this->assertTrue($result->is_active);
    }

    #[Test]
    public function testItCannotFindInactiveNumeratorProfileByTypeWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $this->expectException(ModelNotFoundException::class);

        $this->numeratorProfileService->findNumeratorProfileByType($numeratorProfile->type->name, onlyActive: true);
    }

    #[Test]
    public function testItCanGetCounterWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active()->createOne();

        $result = $this->numeratorProfileService->getCounter($numeratorProfile->type->name, onlyActive: true);

        $this->assertTrue($result->is($numeratorProfile));
        $this->assertTrue($result->is_active);
    }

    #[Test]
    public function testItCannotGetInactiveCounterWithOnlyActiveTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $this->expectException(ModelNotFoundException::class);

        $this->numeratorProfileService->getCounter($numeratorProfile->type->name, onlyActive: true);
    }

    #[Test]
    public function testHasActiveSequenceReturnsTrueWhenActiveSequenceExists(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $hasActiveSequence = $this->numeratorProfileService->hasActiveSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertTrue($hasActiveSequence);
    }

    #[Test]
    public function testHasActiveSequenceReturnsFalseWhenOnlyDeletedSequenceExists(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $sequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $sequence->delete();

        $hasActiveSequence = $this->numeratorProfileService->hasActiveSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertFalse($hasActiveSequence);
    }

    #[Test]
    public function testHasDeletedSequenceReturnsTrueWhenDeletedSequenceExists(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $sequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $sequence->delete();

        $hasDeletedSequence = $this->numeratorProfileService->hasDeletedSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertTrue($hasDeletedSequence);
    }

    #[Test]
    public function testHasDeletedSequenceReturnsFalseWhenOnlyActiveSequenceExists(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $hasDeletedSequence = $this->numeratorProfileService->hasDeletedSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertFalse($hasDeletedSequence);
    }

    #[Test]
    public function testHasSequenceReturnsTrueForBothActiveAndDeletedSequences(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $deletedSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => 'DEL-001',
            ]);
        $deletedSequence->delete();

        $hasActiveSequence = $this->numeratorProfileService->hasSequence($numeratorProfile, $numeratorProfile->formattedNumber);
        $hasDeletedSequence = $this->numeratorProfileService->hasSequence($numeratorProfile, 'DEL-001');

        $this->assertTrue($hasActiveSequence);
        $this->assertTrue($hasDeletedSequence);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testUpdateNumeratorProfileWithIsActiveField(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->active(false)->createOne();

        $data = [
            'is_active' => true,
        ];

        $updatedNumeratorProfile = $this->numeratorProfileService->updateNumeratorProfile($numeratorProfile, $data);

        $this->assertTrue($updatedNumeratorProfile->is_active);
    }

    /**
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testGetNextAvailableNumberWhenAllNumbersUsed(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'min' => 1,
            'max' => 2,
        ]);

        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne([
            'counter' => 1,
        ]);

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => Formatter::format($numeratorProfile->format, 1, $numeratorProfile->prefix, $numeratorProfile->suffix, $numeratorProfile->pad_length),
            ]);

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => Formatter::format($numeratorProfile->format, 2, $numeratorProfile->prefix, $numeratorProfile->suffix, $numeratorProfile->pad_length),
            ]);

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorProfileService->advanceCounter($numeratorProfile, 1);
    }

    #[Test]
    public function testDeleteNumeratorSequencesCallsSequenceService(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->count(3)
            ->create();

        $numeratorProfile->refresh();
        $initialSequenceCount = $numeratorProfile->sequences->count();

        $this->assertEquals(3, $initialSequenceCount);

        $this->numeratorProfileService->deleteNumeratorProfile($numeratorProfile);

        $this->assertSoftDeleted(NumeratorProfile::getModel()->getTable(), ['id' => $numeratorProfile->id]);

        $remainingSequences = NumeratorSequence::where('profile_id', $numeratorProfile->id)->count();
        $this->assertEquals(0, $remainingSequences);
    }

    #[Test]
    public function testGetNumeratorProfilesWithRelations(): void
    {
        NumeratorProfileFactory::new()->withRequired()->createOne();

        $result = $this->numeratorProfileService->getNumeratorProfiles(['type', 'sequences']);

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('type'));
        $this->assertTrue($result->first()->relationLoaded('sequences'));
    }

    #[Test]
    public function testHasSequenceWithExcludedModelId(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $excludedModelId = strtolower(Str::ulid());

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'model_id'         => $excludedModelId,
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $hasSequence = $this->numeratorProfileService->hasSequence(
            $numeratorProfile,
            $numeratorProfile->formattedNumber,
            $excludedModelId,
        );

        $this->assertFalse($hasSequence);

        $hasSequenceWithoutExclusion = $this->numeratorProfileService->hasSequence(
            $numeratorProfile,
            $numeratorProfile->formattedNumber,
        );

        $this->assertTrue($hasSequenceWithoutExclusion);
    }

    #[Test]
    public function testHasActiveSequenceWithExcludedModelId(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $excludedModelId = strtolower(Str::ulid());

        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'model_id'         => $excludedModelId,
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);

        $hasActiveSequence = $this->numeratorProfileService->hasActiveSequence(
            $numeratorProfile,
            $numeratorProfile->formattedNumber,
            $excludedModelId,
        );

        $this->assertFalse($hasActiveSequence);
    }

    #[Test]
    public function testHasDeletedSequenceWithExcludedModelId(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        $excludedModelId = strtolower(Str::ulid());

        $sequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'model_id'         => $excludedModelId,
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $sequence->delete();

        $hasDeletedSequence = $this->numeratorProfileService->hasDeletedSequence(
            $numeratorProfile,
            $numeratorProfile->formattedNumber,
            $excludedModelId,
        );

        $this->assertFalse($hasDeletedSequence);
    }

    #[Test]
    public function testGetNextAvailableNumberRespectsReuseIfDeletedFalse(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->withReuse(false)->createOne([
            'counter' => 1,
        ]);

        $sequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $sequence->delete();

        $this->numeratorProfileService->advanceCounter($numeratorProfile, 1);

        $numeratorProfile->refresh();

        $this->assertEquals(2, $numeratorProfile->counter);
    }

    #[Test]
    public function testGetNextAvailableNumberRespectsReuseIfDeletedTrue(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->withReuse()->createOne([
            'counter' => 1,
        ]);

        $sequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => $numeratorProfile->formattedNumber,
            ]);
        $sequence->delete();

        $this->numeratorProfileService->advanceCounter($numeratorProfile, 1);

        $numeratorProfile->refresh();

        $this->assertEquals(1, $numeratorProfile->counter);
    }
}
