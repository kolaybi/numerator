<?php

namespace KolayBi\Numerator\Tests\Services;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
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

        $this->tenantIdColumn = Config::get('numerator.database.tenant_id_column', 'tenant_id');
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
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanUpdateNumeratorProfileExceptForStart(): void
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
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanUpdateStartValueOfTheNumeratorProfileAndUpdateCounter(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();
        NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne([
                'formatted_number' => Formatter::format(
                    format: $numeratorProfile->format,
                    number: $numeratorProfile->start + 1,
                    prefix: $numeratorProfile->prefix,
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
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanThrowsOutOfBoundsExceptionForMinValueWhileUpdating(): void
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
     * @throws OutOfBoundsException
     */
    #[Test]
    public function testItCanThrowsOutOfBoundsExceptionForMaxValueWhileUpdating(): void
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
    public function testItCanThrowsOutOfBoundsExceptionWhileAdvanceCounter(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne([
            'max' => 10,
        ]);
        $numeratorProfile = NumeratorProfileFactory::new()->withType($numeratorType)->createOne();

        $this->expectException(OutOfBoundsException::class);

        $this->numeratorProfileService->advanceCounter($numeratorProfile, number: $numeratorType->max + 1);
    }

    #[Test]
    public function testItCanAttachExistingTenants(): void
    {
        NumeratorProfileFactory::times(3)->withRequired()->create();

        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $this->numeratorProfileService->attachExistingTenants([
            'type_id' => $numeratorType->id,
            'format'  => NumeratorFormatVariable::NUMBER->value,
            'start'   => $numeratorType->min,
            'counter' => $numeratorType->min,
        ]);

        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1 + 1);
        $this->assertCount(3, $numeratorType->profiles->toArray());
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
    public function testItCanHasSequence(): void
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
    public function testItCanHasNotSequence(): void
    {
        $numeratorProfile = NumeratorProfileFactory::new()->withRequired()->createOne();

        $hasSequence = $this->numeratorProfileService->hasSequence($numeratorProfile, $numeratorProfile->formattedNumber);

        $this->assertFalse($hasSequence);
    }

    #[Test]
    public function testItCanHasSequenceWithModelId(): void
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
            modelId: strtolower(Str::ulid()),
        );

        $this->assertTrue($hasSequence);
    }

    #[Test]
    public function testItCanHasNotSequenceWithModelId(): void
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
            modelId: $modelId,
        );

        $this->assertFalse($hasSequence);
    }
}
