<?php
namespace App\Modules\Bookings\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case ApiBookingStore = 'ApiBookingService.store';
    case ApiBookingCancel = 'ApiBookingService.cancel';
    case AdminOrderAdminServiceUpdate = 'AdminServiceOrderAdminService.update';
    case ApiBookingRatingRateBooking = 'ApiBookingRatingService.rateBooking';
    case AdminOrderCreationStoreService = 'AdminOrderCreationService.storeService';
}
