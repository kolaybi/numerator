<?php

namespace KolayBi\Numerator\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use KolayBi\Numerator\Exceptions\OutOfBoundsException;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Scopes\TenantIdScope;
use KolayBi\Numerator\Utils\Formatter;

class NumeratorProfileService
{
    /**
     * @return Collection<NumeratorProfile>
     */
    public function getNumeratorProfiles(array $relations = []): Collection
    {
        $profile = NumeratorProfile::latest()->get();

        $profile->load($relations);

        return $profile;
    }

    public function getNumeratorProfile(string $id, array $relations = []): NumeratorProfile
    {
        return $this->findNumeratorProfile($id)->load($relations);
    }

    public function createNumeratorProfile(string $tenantId, array $data): NumeratorProfile
    {
        $data = array_merge($data, [
            config('numerator.database.tenant_id_column') => $tenantId,
        ]);

        return NumeratorProfile::create($data);
    }

    /**
     * @throws OutOfBoundsException
     */
    public function updateNumeratorProfile(NumeratorProfile|string $profile, array $data): NumeratorProfile
    {
        $profile = $this->findNumeratorProfile($profile, lock: true);

        $data = Arr::only($data, ['prefix', 'format', 'start']);

        if ($start = Arr::get($data, 'start')) {
            if (!$this->isWithinInterval($profile->type, $start)) {
                throw new OutOfBoundsException();
            }

            Arr::set($data, 'counter', $this->getNextAvailableNumber($profile, $start));
        }

        $profile->update($data);

        return $profile->refresh();
    }

    public function deleteNumeratorProfile(NumeratorProfile|string $profile): void
    {
        $profile = $this->findNumeratorProfile($profile, lock: true);

        $this->deleteNumeratorSequences($profile);

        $profile->delete();
    }

    /**
     * @throws OutOfBoundsException
     */
    public function advanceCounter(NumeratorProfile $profile, int $number): void
    {
        $profile->counter = $this->getNextAvailableNumber($profile, $number);
        $profile->update();
    }

    public function attachExistingTenants(array $data): void
    {
        $tenantIdColumn = config('numerator.database.tenant_id_column');

        NumeratorProfile::withoutGlobalScope(TenantIdScope::class)
            ->get($tenantIdColumn)
            ->unique($tenantIdColumn)
            ->each(fn($profile) => $this->createNumeratorProfile($profile->$tenantIdColumn, $data));
    }

    public function findNumeratorProfile(NumeratorProfile|string $profile, ?bool $lock = null): NumeratorProfile
    {
        if (is_string($profile)) {
            return NumeratorProfile::lock($lock)->findOrFail($profile);
        }

        if ($profile instanceof NumeratorProfile && $lock) {
            return NumeratorProfile::lock($lock)->findOrFail($profile->id);
        }

        return $profile;
    }

    public function findNumeratorProfileByType(string $type, ?bool $lock = null): NumeratorProfile
    {
        $numeratorTypeService = new NumeratorTypeService();
        $numeratorType = $numeratorTypeService->findNumeratorTypeByName($type);

        return NumeratorProfile::lock($lock)
            ->where('type_id', '=', $numeratorType->id)
            ->firstOrFail();
    }

    public function getCounter(string $type): NumeratorProfile
    {
        return $this->findNumeratorProfileByType($type);
    }

    public function hasSequence(NumeratorProfile $profile, string $formattedNumber, ?string $excludedModelId = null): bool
    {
        return $profile->sequences
            ->when(!is_null($excludedModelId), fn(Collection $builder) => $builder->where('model_id', '<>', $excludedModelId))
            ->where('formatted_number', '=', $formattedNumber)
            ->isNotEmpty();
    }

    public function isWithinInterval(NumeratorType $type, int $startNumber): bool
    {
        return $type->min <= $startNumber && $startNumber <= $type->max;
    }

    /**
     * @throws OutOfBoundsException
     */
    private function getNextAvailableNumber(NumeratorProfile $profile, int $number): int
    {
        $profile->loadMissing(['sequences', 'type']);

        for (; $number <= $profile->type->max; $number++) {
            $formattedNumber = Formatter::format($profile->format, $number, $profile->prefix);

            if ($this->hasSequence($profile, $formattedNumber)) {
                continue;
            }

            return $number;
        }

        throw new OutOfBoundsException();
    }

    private function deleteNumeratorSequences(NumeratorProfile $numeratorProfile): void
    {
        $numeratorSequenceService = new NumeratorSequenceService();

        foreach ($numeratorProfile->sequences as $sequence) {
            $numeratorSequenceService->deleteNumeratorSequence($sequence);
        }
    }
}
