<?php

namespace KolayBi\Numerator\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use KolayBi\Numerator\Exceptions\InvalidFormatException;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Utils\FormatUtil;

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

    /**
     * @throws InvalidFormatException
     */
    public function createNumeratorType(array $data): NumeratorType
    {
        if (!FormatUtil::isValidFormat(Arr::get($data, 'format'), nullable: true)) {
            throw new InvalidFormatException();
        }

        /** @var NumeratorType $numeratorType */
        $numeratorType = NumeratorType::create($data);

        $numeratorProfileService = new NumeratorProfileService();
        $numeratorProfileService->attachExistingTenants([
            'type_id'    => $numeratorType->id,
            'prefix'     => $numeratorType->prefix,
            'suffix'     => $numeratorType->suffix,
            'format'     => $numeratorType->format,
            'pad_length' => $numeratorType->pad_length,
            'start'      => $numeratorType->min,
            'counter'    => $numeratorType->min,
        ]);

        return $numeratorType;
    }

    public function updateNumeratorType(NumeratorType|string $numeratorType, array $data): NumeratorType
    {
        $type = $this->findNumeratorType($numeratorType, lock: true);

        $type->update($data);

        return $type->refresh();
    }

    public function deleteNumeratorType(NumeratorType|string $numeratorType): void
    {
        $type = $this->findNumeratorType($numeratorType, lock: true);

        $this->deleteNumeratorProfiles($type);

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

    /**
     * @throws InvalidFormatException
     */
    public function attachExistingNumeratorTypes(string $tenantId): void
    {
        $numeratorTypes = $this->getNumeratorTypes();

        $numeratorProfileService = new NumeratorProfileService();
        foreach ($numeratorTypes as $numeratorType) {
            $numeratorProfileService->createNumeratorProfile($tenantId, [
                'type_id'    => $numeratorType->id,
                'prefix'     => $numeratorType->prefix,
                'suffix'     => $numeratorType->suffix,
                'format'     => $numeratorType->format,
                'pad_length' => $numeratorType->pad_length,
                'start'      => $numeratorType->min,
                'counter'    => $numeratorType->min,
            ]);
        }
    }

    private function deleteNumeratorProfiles(NumeratorType $numeratorType): void
    {
        $numeratorProfileService = new NumeratorProfileService();

        foreach ($numeratorType->profiles as $profile) {
            $numeratorProfileService->deleteNumeratorProfile($profile);
        }
    }
}
