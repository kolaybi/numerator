<?php

namespace KolayBi\Numerator\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use KolayBi\Numerator\Exceptions\InvalidFormatException;
use KolayBi\Numerator\Exceptions\OutOfBoundsException;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Scopes\TenantIdScope;
use KolayBi\Numerator\Utils\Formatter;
use KolayBi\Numerator\Utils\FormatUtil;

class NumeratorProfileService
{
    private const CANDIDATE_CHUNK_SIZE = 500;

    /**
     * @return Collection<NumeratorProfile>
     */
    public function getNumeratorProfiles(array $relations = [], bool $onlyActive = false): Collection
    {
        $profile = NumeratorProfile::latest()
            ->when($onlyActive, fn(Builder $builder) => $builder->where('is_active', '=', true))
            ->get();

        $profile->load($relations);

        return $profile;
    }

    public function getNumeratorProfile(string $id, array $relations = [], bool $onlyActive = false): NumeratorProfile
    {
        return $this->findNumeratorProfile($id, onlyActive: $onlyActive)->load($relations);
    }

    /**
     * @throws InvalidFormatException
     */
    public function createNumeratorProfile(string $tenantId, array $data): NumeratorProfile
    {
        if (!FormatUtil::isValidFormat(Arr::get($data, 'format'))) {
            throw new InvalidFormatException();
        }

        $data = array_merge($data, [
            config('numerator.database.tenant_id_column') => $tenantId,
        ]);

        return NumeratorProfile::create($data);
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfBoundsException
     */
    public function updateNumeratorProfile(NumeratorProfile|string $profile, array $data): NumeratorProfile
    {
        $profile = $this->findNumeratorProfile($profile, lock: true);

        $data = Arr::only($data, ['prefix', 'suffix', 'format', 'pad_length', 'start', 'is_active', 'reuse_if_deleted']);

        if (Arr::has($data, 'format') && !FormatUtil::isValidFormat(Arr::get($data, 'format'))) {
            throw new InvalidFormatException();
        }

        $start = Arr::get($data, 'start');

        if ($start && !$this->isWithinInterval($profile->type, $start)) {
            throw new OutOfBoundsException();
        }

        $profile->fill($data);

        if ($start) {
            $profile->counter = $this->getNextAvailableNumber($profile, $start);
        } elseif ($profile->isDirty(['prefix', 'suffix', 'format', 'pad_length', 'reuse_if_deleted'])) {
            $profile->counter = $this->getNextAvailableNumber($profile, $profile->counter);
        }

        $profile->save();

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

    /**
     * @throws InvalidFormatException
     */
    public function attachExistingTenants(array $data): void
    {
        $tenantIdColumn = config('numerator.database.tenant_id_column');

        NumeratorProfile::withoutGlobalScope(TenantIdScope::class)
            ->get($tenantIdColumn)
            ->unique($tenantIdColumn)
            ->each(fn($profile) => $this->createNumeratorProfile($profile->{$tenantIdColumn}, $data));
    }

    public function findNumeratorProfile(NumeratorProfile|string $profile, ?bool $lock = null, bool $onlyActive = false): NumeratorProfile
    {
        if (is_string($profile)) {
            return NumeratorProfile::lock($lock)
                ->when($onlyActive, fn(Builder $builder) => $builder->where('is_active', '=', true))
                ->findOrFail($profile);
        }

        if ($profile instanceof NumeratorProfile && ($lock || ($onlyActive && !$profile->is_active))) {
            return NumeratorProfile::lock($lock)
                ->when($onlyActive, fn(Builder $builder) => $builder->where('is_active', '=', true))
                ->findOrFail($profile->id);
        }

        return $profile;
    }

    public function findNumeratorProfileByType(string $type, ?bool $lock = null, bool $onlyActive = false): NumeratorProfile
    {
        $numeratorTypeService = new NumeratorTypeService();
        $numeratorType = $numeratorTypeService->findNumeratorTypeByName($type);

        return NumeratorProfile::lock($lock)
            ->where('type_id', '=', $numeratorType->id)
            ->when($onlyActive, fn(Builder $builder) => $builder->where('is_active', '=', true))
            ->firstOrFail();
    }

    public function getCounter(string $type, bool $onlyActive = false): NumeratorProfile
    {
        return $this->findNumeratorProfileByType($type, onlyActive: $onlyActive);
    }

    public function hasSequence(NumeratorProfile $profile, string $formattedNumber, ?string $excludedModelId = null): bool
    {
        $query = $profile->sequences()
            ->withTrashed()
            ->where('formatted_number', '=', $formattedNumber);

        if (!is_null($excludedModelId)) {
            $query->where('model_id', '<>', $excludedModelId);
        }

        return $query->exists();
    }

    public function hasActiveSequence(NumeratorProfile $profile, string $formattedNumber, ?string $excludedModelId = null): bool
    {
        $query = $profile->sequences()
            ->where('formatted_number', '=', $formattedNumber);

        if (!is_null($excludedModelId)) {
            $query->where('model_id', '<>', $excludedModelId);
        }

        return $query->exists();
    }

    public function hasDeletedSequence(NumeratorProfile $profile, string $formattedNumber, ?string $excludedModelId = null): bool
    {
        $query = $profile->sequences()
            ->onlyTrashed()
            ->where('formatted_number', '=', $formattedNumber);

        if (!is_null($excludedModelId)) {
            $query->where('model_id', '<>', $excludedModelId);
        }

        return $query->exists();
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
        $profile->loadMissing(['type']);
        $max = $profile->type->max;

        while ($number <= $max) {
            $last = $number + min(self::CANDIDATE_CHUNK_SIZE - 1, $max - $number);

            $candidates = [];
            for ($candidate = $number; $candidate <= $last; $candidate++) {
                $candidates[$candidate] = Formatter::format($profile->format, $candidate, $profile->prefix, $profile->suffix, $profile->pad_length);
            }

            // The column collation decides equality (e.g. "er-7" equals "ER-7" under utf8mb4_unicode_ci), so matches PHP can't confirm are asked to the database.
            $usedNumbers = array_flip($this->usedSequences($profile)->whereIn('formatted_number', $candidates)->pluck('formatted_number')->all());
            $hasInexactMatches = !empty(array_diff_key($usedNumbers, array_flip($candidates)));

            foreach ($candidates as $candidate => $formattedNumber) {
                if (isset($usedNumbers[$formattedNumber])) {
                    continue;
                }

                if (!$hasInexactMatches || !$this->usedSequences($profile)->where('formatted_number', '=', $formattedNumber)->exists()) {
                    return $candidate;
                }
            }

            if ($last === $max) {
                break;
            }

            $number = $last + 1;
        }

        throw new OutOfBoundsException();
    }

    private function usedSequences(NumeratorProfile $profile): HasMany
    {
        return $profile->reuse_if_deleted ? $profile->sequences() : $profile->sequences()->withTrashed();
    }

    private function deleteNumeratorSequences(NumeratorProfile $numeratorProfile): void
    {
        $numeratorSequenceService = new NumeratorSequenceService();

        foreach ($numeratorProfile->sequences as $sequence) {
            $numeratorSequenceService->deleteNumeratorSequence($sequence);
        }
    }
}
