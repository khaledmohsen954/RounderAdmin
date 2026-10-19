<?php
namespace App\Modules\Notifications\Enums;
/** Fixed event kinds; permissions are evaluated again whenever notifications are read. */
enum AlertType: string {
    case Emergency = 'emergency';
    case Property = 'property';
    case ResidentLinked = 'resident_linked';
    case Booking = 'booking';
    case Provider = 'provider';
    /** @return string */
    public function permission(): string {
        return match($this) {
            self::Emergency=>'emergencies.view', self::Property,self::ResidentLinked=>'realestate.view',
            self::Booking=>'orders.view', self::Provider=>'providers.view',
        };
    }
    /** @return string */
    public function route(): string {
        return match($this) {
            self::Emergency=>'admin.emergencies.index', self::Property,self::ResidentLinked=>'admin.realestate.index',
            self::Booking=>'admin.service-orders.index', self::Provider=>'admin.providers.index',
        };
    }
    /** Translation keys keep persisted data independent of the viewer language. @return string */
    public function label(): string {
        return match($this) {
            self::Emergency=>'New emergency request', self::Property=>'New property',
            self::ResidentLinked=>'Resident linked to a property', self::Booking=>'New service order',
            self::Provider=>'New service provider',
        };
    }
}
