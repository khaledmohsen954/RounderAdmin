<?php
namespace App\Modules\Catalog\Services;
use App\Modules\Catalog\Models\PackageFeature;
use App\Modules\Catalog\Models\ServicePackage;
use App\Modules\Catalog\Events\CatalogChanged;
use App\Modules\Catalog\Enums\Operation;
use Illuminate\Support\Facades\DB;

/** تعريفات مقارنة الباقات وتفاصيلها؛ لا تنشئ حجوزات أو حصصاً قابلة للاستهلاك. */
class PackageFeatureService
{
    /**
     * @param array<string,mixed> $input
     * @param int|null $featureId
     * @param int|null $actorId
     * @return string
     * @throws \Throwable
     */
    public function save(array $input, ?int $featureId, ?int $actorId): string
    {
        return DB::transaction(function () use ($input,$featureId,$actorId) {
            if ($featureId !== null) {
                PackageFeature::whereKey($featureId)->lockForUpdate()->firstOrFail()->update($input);
            } else {
                PackageFeature::create($input);
            }
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::SavePackageFeature,$actorId)));
            return __('Comparison feature saved.');
        });
    }

    /**
     * تحديث قيم الباقة داخل المعاملة نفسها؛ غياب الحقل يحافظ على البيانات القديمة.
     * @param ServicePackage $package
     * @param array<int,array<string,mixed>>|null $features
     * @return void
     * @throws \Throwable
     */
    public function sync(ServicePackage $package, ?array $features): void
    {
        if ($features === null) {
            return;
        }
        DB::transaction(function () use ($package, $features) {
            $package->comparisonFeatures()->whereNotIn('package_feature_id',array_column($features,'package_feature_id'))->delete();
            foreach ($features as $value) {
                $package->comparisonFeatures()->updateOrCreate(
                    ['package_feature_id'=>$value['package_feature_id']],
                    ['availability'=>$value['availability'],
                     'value_ar'=>$value['value_ar'] ?? null,'value_en'=>$value['value_en'] ?? null,'value_ur'=>$value['value_ur'] ?? null]
                );
            }
        });
    }
}
