<?php
namespace App\Modules\Notifications\Observers;
use App\Modules\Notifications\Services\AdminAlertService;
use App\Modules\Notifications\Enums\AlertType;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Properties\Models\Home;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Model;
/** Capture creations from both admin forms and API workflows without duplicating business rules. */
class AdminActivityObserver {
    /** @param AdminAlertService $alerts */
    public function __construct(private AdminAlertService $alerts) {}
    /** @param Model $model @return void @throws \Throwable */
    public function created(Model $model): void {
        $type=match(true) {
            $model instanceof EmergencyRequest=>AlertType::Emergency,
            $model instanceof Home=>AlertType::Property,
            $model instanceof Booking=>AlertType::Booking,
            $model instanceof ProviderProfile=>AlertType::Provider,
            default=>null,
        };
        if($type) $this->alerts->record($type,(int)$model->getKey());
    }
}
