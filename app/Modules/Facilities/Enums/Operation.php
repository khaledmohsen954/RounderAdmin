<?php
namespace App\Modules\Facilities\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminFacilityAdminUpdateFacilityReport = 'AdminFacilityAdminService.updateFacilityReport';
    case ApiFacilityCreateFacilityReport = 'ApiFacilityService.createFacilityReport';
}
