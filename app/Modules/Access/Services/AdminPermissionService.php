<?php
namespace App\Modules\Access\Services;
use App\Modules\Identity\Models\Admin;
use App\Modules\Access\Enums\{DashboardSection, PermissionAction};

/** المصدر الوحيد لربط المسارات بمصفوفة صلاحيات الإدارة. */
final class AdminPermissionService
{
    /** @return array<string> */
    public function keys(): array
    {
        $keys = [];
        foreach (DashboardSection::cases() as $section) {
            foreach (PermissionAction::cases() as $action) $keys[] = $section->value.'.'.$action->value;
        }
        return $keys;
    }

    /** @param mixed $actor @param string $key @return bool */
    public function allows(mixed $actor, string $key): bool
    {
        if (!$actor instanceof Admin || !$actor->is_active) return false;
        return $actor->is_super_admin || in_array($key, $actor->permissionSet?->permissions ?? [], true);
    }

    /**
     * المسارات غير المسجلة مرفوضة افتراضياً؛ تسجيل الخروج متاح لكل مدير نشط.
     * @param mixed $actor @param string $route @param string $method @return bool
     */
    public function allowsRoute(mixed $actor, string $route, string $method): bool
    {
        if (!$actor instanceof Admin || !$actor->is_active) return false;
        if ($route === 'admin.logout') return true;
        $section = match (true) {
            $route === 'admin.dashboard' => DashboardSection::Dashboard,
            $route === 'admin.about' => DashboardSection::About,
            str_starts_with($route, 'admin.users.') => DashboardSection::Users,
            str_starts_with($route, 'admin.providers.') => DashboardSection::Providers,
            str_starts_with($route, 'admin.realestate.') => DashboardSection::Realestate,
            str_starts_with($route, 'admin.packages.'), str_starts_with($route, 'admin.services.'),
                str_starts_with($route, 'admin.package-features.') => DashboardSection::Catalog,
            str_starts_with($route, 'admin.content.') => DashboardSection::Content,
            str_starts_with($route, 'admin.service-orders.') => DashboardSection::Orders,
            str_starts_with($route, 'admin.emergencies.') => DashboardSection::Emergencies,
            str_starts_with($route, 'admin.permissions.') => DashboardSection::Permissions,
            default => null,
        };
        if (!$section) return false;
        $action = match (true) {
            $method === 'DELETE' => PermissionAction::Delete,
            in_array($method, ['PUT','PATCH'], true), str_ends_with($route, '.edit') => PermissionAction::Update,
            $method === 'POST', str_ends_with($route, '.create') => PermissionAction::Create,
            default => PermissionAction::View,
        };
        return $this->allows($actor, $section->value.'.'.$action->value);
    }
}
