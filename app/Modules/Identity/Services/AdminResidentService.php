<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Enums\RoleCode;

class AdminResidentService
{
    /**
     * استعلام حسابات السكان المسجلين في التطبيق.
     *
     * @param array<string, mixed> $input
     * @param Admin|null $actor
     * @return array<string, mixed>
     */
    public function index(array $input, ?Admin $actor): array
    {
        $residents = User::query()
            ->whereHas('role', fn ($q) => $q->where('code', RoleCode::Resident->value))
            ->with(['residentHomes.city.translations'])
            ->withCount(['bookings', 'residentHomes'])
            ->when(!empty($input['q']), function ($q) use ($input) {
                $term = trim($input['q']);
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->latest('id')
            ->paginate((int) ($input['per_page'] ?? 15))
            ->withQueryString();

        return compact('residents');
    }
}
