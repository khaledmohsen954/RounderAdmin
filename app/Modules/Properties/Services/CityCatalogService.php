<?php
namespace App\Modules\Properties\Services;
use App\Modules\Properties\Models\City;
/** مصدر المدن للتكامل؛ يعيد المعرّف المحلي المستخدم في foreign keys. */
final class CityCatalogService
{
    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function index(array $input): array
    {
        $query = City::available()->with('translations')->orderBy('id');
        if (!empty($input['search'])) {
            $search = $input['search'];
            $query->whereHas('translations', fn ($q) => $q->where('name', 'like', '%'.$search.'%'));
        }
        $page = $query->paginate($input['per_page'] ?? 50);
        return ['data'=>$page->getCollection()->map(fn (City $city) => [
            'id'=>$city->id, 'code'=>$city->code, 'country_code'=>$city->country_code->value,
            'name'=>$city->translations->firstWhere('locale', app()->getLocale())?->name
                ?? $city->translations->firstWhere('locale','ar')?->name ?? $city->code,
        ]), 'meta'=>['current_page'=>$page->currentPage(), 'last_page'=>$page->lastPage(),
            'per_page'=>$page->perPage(), 'total'=>$page->total()]];
    }
}
