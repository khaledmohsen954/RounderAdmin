<?php

namespace App\Modules\Emergencies\Services;

use App\Modules\Emergencies\Models\EmergencyType;
use App\Modules\Identity\Models\User;

/** Read the active emergency type catalog for residents. */
class ApiEmergencyTypeService
{
    /**
     * @param array<string, mixed> $input
     * @param User|null $actor
     * @return array{data: \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>}
     */
    public function index(array $input, ?User $actor): array
    {
        $locale = app()->getLocale();

        $types = EmergencyType::query()
            ->active()
            ->with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (EmergencyType $type) => [
                'id' => $type->id,
                'code' => $type->code,
                'name' => $type->translations->firstWhere('locale', $locale)?->name ?? $type->code,
            ])
            ->values();

        return ['data' => $types];
    }
}
