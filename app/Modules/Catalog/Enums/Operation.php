<?php
namespace App\Modules\Catalog\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminPackageAdminUpdatePackage = 'AdminPackageAdminService.updatePackage';
    case AdminPackageAdminStoreService = 'AdminPackageAdminService.storeService';
    case AdminPackageAdminStorePackage = 'AdminPackageAdminService.storePackage';
    case AdminPackageAdminUpdateService = 'AdminPackageAdminService.updateService';
    case ApiResidentPackageSubscribe = 'ApiResidentPackageService.subscribe';
    case ApiResidentPackageCancel = 'ApiResidentPackageService.cancel';
}
