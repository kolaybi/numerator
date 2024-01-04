<?php

namespace KolayBi\Numerator\Services;

use Illuminate\Database\Eloquent\Collection;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
use KolayBi\Numerator\Models\NumeratorType;

class NumeratorTypeService
{
    /**
     * @return Collection<NumeratorType>
     */
    public function getNumeratorTypes(): Collection
    {
        return NumeratorType::latest()->get();
    }

    public function getNumeratorType(string $id): NumeratorType
    {
        return $this->findNumeratorType($id);
    }

    public function createNumeratorType(array $data): NumeratorType
    {
        $numeratorType = NumeratorType::create($data);

        $numeratorProfileService = new NumeratorProfileService();
        $numeratorProfileService->attachExistingTenants([
            'type_id' => $numeratorType->id,
            'format'  => NumeratorFormatVariable::NUMBER->value,
            'start'   => $numeratorType->min,
            'counter' => $numeratorType->min,
        ]);

        return $numeratorType;
    }

    public function updateNumeratorType(string $id, array $data): NumeratorType
    {
        $type = $this->findNumeratorType($id, lock: true);

        $type->update($data);

        return $type->refresh();
    }

    public function deleteNumeratorType(string $id): void
    {
        $type = $this->findNumeratorType($id, lock: true);

        $type->delete();
    }

    public function findNumeratorTypeByName(string $name, ?bool $lock = null): NumeratorType
    {
        return NumeratorType::lock($lock)
            ->where('name', '=', $name)
            ->firstOrFail();
    }

    public function findNumeratorType(NumeratorType|string $numeratorType, ?bool $lock = null): NumeratorType
    {
        if (is_string($numeratorType)) {
            return NumeratorType::lock($lock)->findOrFail($numeratorType);
        }

        if ($numeratorType instanceof NumeratorType && $lock) {
            return NumeratorType::lock($lock)->findOrFail($numeratorType->id);
        }

        return $numeratorType;
    }

    public function attachExistingNumeratorTypes(string $tenantId): void
    {
        $numeratorTypes = $this->getNumeratorTypes();

        $numeratorProfileService = new NumeratorProfileService();
        foreach ($numeratorTypes as $numeratorType) {
            $numeratorProfileService->createNumeratorProfile($tenantId, [
                'type_id' => $numeratorType->id,
                'format'  => NumeratorFormatVariable::NUMBER->value,
                'start'   => $numeratorType->min,
                'counter' => $numeratorType->min,
            ]);
        }
    }
}
