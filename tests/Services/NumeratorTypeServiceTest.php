<?php

namespace KolayBi\Numerator\Tests\Services;

use Database\Factories\NumeratorProfileFactory;
use Database\Factories\NumeratorSequenceFactory;
use Database\Factories\NumeratorTypeFactory;
use Illuminate\Support\Str;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorSequence;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Services\NumeratorTypeService;
use KolayBi\Numerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumeratorTypeService::class)]
class NumeratorTypeServiceTest extends TestCase
{
    private NumeratorTypeService $numeratorTypeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numeratorTypeService = new NumeratorTypeService();
    }

    #[Test]
    public function testItCanGetNumeratorTypes()
    {
        $numeratorTypes = NumeratorTypeFactory::times(10)->create();

        $result = $this->numeratorTypeService->getNumeratorTypes();

        $this->assertCount(10, $result->toArray());
        foreach ($numeratorTypes as $numeratorType) {
            $this->assertTrue($result->contains($numeratorType));
        }
    }

    #[Test]
    public function testItCanGetANumeratorType(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $result = $this->numeratorTypeService->getNumeratorType($numeratorType->id);

        $this->assertTrue($result->is($numeratorType));
    }

    #[Test]
    public function testItCanCreateNumeratorType(): void
    {
        $numeratorTypeData = NumeratorTypeFactory::new()->makeOne()->getAttributes();

        $this->numeratorTypeService->createNumeratorType($numeratorTypeData);

        $this->assertDatabaseHas(NumeratorType::getModel()->getTable(), $numeratorTypeData);
        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1);
    }

    #[Test]
    public function testItCanCreateNewNumeratorTypeForExistingTenants(): void
    {
        NumeratorProfileFactory::times(3)->withRequired()->create();

        $numeratorTypeData = NumeratorTypeFactory::new()->makeOne()->getAttributes();

        $numeratorType = $this->numeratorTypeService->createNumeratorType($numeratorTypeData);

        $this->assertDatabaseHas(NumeratorType::getModel()->getTable(), $numeratorTypeData);
        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 1 + 1);

        $this->assertCount(3, $numeratorType->profiles->toArray());
    }

    #[Test]
    public function testItCanCreateUpdateNumeratorType(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $data = [
            'min' => fake()->numberBetween(100, 150),
        ];

        $updatedNumeratorType = $this->numeratorTypeService->updateNumeratorType($numeratorType, $data);

        $this->assertTrue($updatedNumeratorType->is($numeratorType));
        $this->assertSame($updatedNumeratorType->min, $data['min']);
    }

    #[Test]
    public function testItCanDeleteANumeratorType(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $numeratorProfile = NumeratorProfileFactory::new()
            ->withType($numeratorType)
            ->createOne();

        $numeratorSequence = NumeratorSequenceFactory::new()
            ->for($numeratorProfile, 'profile')
            ->createOne();

        $this->numeratorTypeService->deleteNumeratorType($numeratorType);

        $this->assertSoftDeleted(NumeratorType::getModel()->getTable(), ['id' => $numeratorType->id]);
        $this->assertSoftDeleted(NumeratorProfile::getModel()->getTable(), ['id' => $numeratorProfile->id]);
        $this->assertSoftDeleted(NumeratorSequence::getModel()->getTable(), ['id' => $numeratorSequence->id]);
    }

    #[Test]
    public function testItCanFindNumeratorTypeByName(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $result = $this->numeratorTypeService->findNumeratorTypeByName($numeratorType->name);

        $this->assertTrue($result->is($numeratorType));
    }

    #[Test]
    public function testItCanFindNumeratorType(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $result = $this->numeratorTypeService->findNumeratorType($numeratorType);

        $this->assertTrue($result->is($numeratorType));
    }

    #[Test]
    public function testItCanFindNumeratorTypeWithLock(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $result = $this->numeratorTypeService->findNumeratorType($numeratorType, lock: true);

        $this->assertTrue($result->is($numeratorType));
    }

    #[Test]
    public function testItCanFindNumeratorTypeWithId(): void
    {
        $numeratorType = NumeratorTypeFactory::new()->createOne();

        $result = $this->numeratorTypeService->findNumeratorType($numeratorType->id);

        $this->assertTrue($result->is($numeratorType));
    }

    #[Test]
    public function testItCanAttachExistingNumeratorTypes(): void
    {
        $tenantId = strtolower(Str::ulid());
        NumeratorTypeFactory::times(3)->create();

        $this->numeratorTypeService->attachExistingNumeratorTypes($tenantId);

        $this->assertDatabaseCount(NumeratorType::getModel()->getTable(), 3);
        $this->assertDatabaseCount(NumeratorProfile::getModel()->getTable(), 3);
        $this->assertDatabaseHas(NumeratorProfile::getModel()->getTable(), [
            config('numerator.database.tenant_id_column') => $tenantId,
        ]);
    }
}
