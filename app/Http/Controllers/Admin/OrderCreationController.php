<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\BookingStatusHistory;
use App\Models\EmergencyRequest;
use App\Models\EmergencyRequestStatus;
use App\Models\EmergencyRequestUpdate;
use App\Models\EmergencyType;
use App\Models\ResidentHome;
use App\Models\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Creates requests on behalf of residents while deriving ownership from a membership ID. */
/** إنشاء طلب بالنيابة عن ساكن مع اشتقاق الملكية والسعر من قاعدة البيانات. */
class OrderCreationController extends Controller
{
    public function serviceForm() { return $this->form(false); }
    public function emergencyForm() { return $this->form(true); }

    private function form(bool $emergency)
    {
        $memberships = ResidentHome::query()
            ->join('users', 'resident_homes.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('homes', 'resident_homes.home_id', '=', 'homes.id')
            ->where('roles.code', 'resident')
            ->select('resident_homes.id', 'resident_homes.unit_label', 'users.name as resident_name',
                'homes.name as home_name')->orderBy('users.name')->get();
        $table = $emergency ? 'emergency_types' : 'services';
        $translations = $emergency ? 'emergency_type_translations' : 'service_translations';
        $foreign = $emergency ? 'emergency_type_id' : 'service_id';
        $options = ($emergency ? EmergencyType::query() : Service::query())->leftJoin($translations, function ($join) use ($table, $translations, $foreign) {
            $join->on("$table.id", '=', "$translations.$foreign")->where("$translations.locale", app()->getLocale());
        })->where("$table.is_active", true)
            ->select("$table.id", "$table.code", "$translations.name")->orderBy("$table.code")->get();
        return view('admin.orders.create', compact('emergency', 'memberships', 'options'));
    }

    private function membership(int $id)
    {
        $membership = ResidentHome::query()->whereKey($id)
            ->whereHas('user.role', fn ($query) => $query->where('code', 'resident'))->lockForUpdate()->first();
        if (!$membership) {
            throw ValidationException::withMessages(['resident_home_id' => __('Select a valid resident unit.')]);
        }
        return $membership;
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'resident_home_id' => ['required', 'integer', 'exists:resident_homes,id'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($data, $request) {
            $membership = $this->membership($data['resident_home_id']);
            $service = Service::query()->where('id', $data['service_id'])->where('is_active', true)->lockForUpdate()->first();
            if (!$service) {
                throw ValidationException::withMessages(['service_id' => __('Choose an active service.')]);
            }
            $status = BookingStatus::query()->where('code', 'pending')->firstOrFail();
            // Prices and resident IDs are always taken from trusted database records.
            $id = Booking::query()->create([
                'resident_id' => $membership->user_id, 'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id, 'service_id' => $service->id,
                'booking_status_id' => $status->id, 'scheduled_at' => $data['scheduled_at'],
                'total_amount' => $service->base_price, 'notes' => $data['notes'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ])->getKey();
            BookingStatusHistory::query()->create([
                'booking_id' => $id, 'booking_status_id' => $status->id,
                'changed_by' => $request->user()->id, 'note' => 'Created by administrator', 'created_at' => now(),
            ]);
        });
        return redirect()->route('admin.service-orders.index')->with('status', __('Service order created successfully.'));
    }

    public function storeEmergency(Request $request)
    {
        $data = $request->validate([
            'resident_home_id' => ['required', 'integer', 'exists:resident_homes,id'],
            'emergency_type_id' => ['required', 'integer', Rule::exists('emergency_types', 'id')->where('is_active', true)],
            'contact_phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'location_details' => ['required', 'string', 'min:4', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'confirm_emergency' => ['required', 'accepted'],
        ]);
        DB::transaction(function () use ($data, $request) {
            $membership = $this->membership($data['resident_home_id']);
            $status = EmergencyRequestStatus::query()->where('code', 'received')->firstOrFail();
            $id = EmergencyRequest::query()->create([
                'resident_id' => $membership->user_id, 'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id, 'emergency_type_id' => $data['emergency_type_id'],
                'status_id' => $status->id, 'reference' => 'EM-'.Str::upper(Str::random(12)),
                'contact_phone' => $data['contact_phone'], 'location_details' => $data['location_details'],
                'description' => $data['description'], 'requested_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ])->getKey();
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $id, 'status_id' => $status->id,
                'updated_by' => $request->user()->id, 'note' => 'Created by administrator', 'created_at' => now(),
            ]);
        });
        return redirect()->route('admin.emergencies.index')->with('status', __('Emergency request received'));
    }
}
