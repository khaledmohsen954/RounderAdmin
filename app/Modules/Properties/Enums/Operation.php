<?php
namespace App\Modules\Properties\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminHomeStore = 'AdminHomeService.store';
    case AdminHomeUpdate = 'AdminHomeService.update';
    case AdminHomeDelete = 'AdminHomeService.delete';
    case ApiResidentHomeLink = 'ApiResidentHomeService.link';
    case ApiVisitCreateVisit = 'ApiVisitService.createVisit';
    case ApiVisitCancelVisit = 'ApiVisitService.cancelVisit';
}
