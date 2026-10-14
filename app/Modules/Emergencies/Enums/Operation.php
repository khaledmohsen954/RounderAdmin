<?php
namespace App\Modules\Emergencies\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminEmergencyAdminUpdate = 'AdminEmergencyAdminService.update';
    case AdminEmergencyCreationStoreEmergency = 'AdminEmergencyCreationService.storeEmergency';
    case ApiEmergencyRequestStore = 'ApiEmergencyRequestService.store';
    case ApiEmergencyRequestCancel = 'ApiEmergencyRequestService.cancel';
}
