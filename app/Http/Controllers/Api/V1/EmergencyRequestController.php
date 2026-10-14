<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmergencyRequestController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $page = DB::table('emergency_requests')
            ->join('emergency_request_statuses', 'emergency_requests.status_id', '=', 'emergency_request_statuses.id')
            ->join('emergency_request_status_translations', function ($join) use ($locale) {
                $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                    ->where('emergency_request_status_translations.locale', '=', $locale);
            })
            ->join('emergency_types', 'emergency_requests.emergency_type_id', '=', 'emergency_types.id')
            ->join('emergency_type_translations', function ($join) use ($locale) {
                $join->on('emergency_types.id', '=', 'emergency_type_translations.emergency_type_id')
                    ->where('emergency_type_translations.locale', '=', $locale);
            })
            ->where('emergency_requests.resident_id', $request->user()->id)
            ->select(
                'emergency_requests.id',
                'emergency_requests.reference',
                'emergency_requests.location_details',
                'emergency_requests.description',
                'emergency_requests.contact_phone',
                'emergency_requests.requested_at',
                'emergency_requests.dispatched_at',
                'emergency_requests.resolved_at',
                'emergency_request_statuses.code as status_code',
                'emergency_request_status_translations.name as status_name',
                'emergency_type_translations.name as type_name'
            )->orderByDesc('emergency_requests.requested_at')
            ->paginate(min(max($request->integer('per_page', 15), 1), 50));

        return response()->json(['data' => $page]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'resident_home_id' => [
                'required', 'integer',
                Rule::exists('resident_homes', 'id')->where('user_id', $request->user()->id),
            ],
            'emergency_type_id' => [
                'required', 'integer',
                Rule::exists('emergency_types', 'id')->where('is_active', true),
            ],
            'contact_phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'location_details' => ['required', 'string', 'min:4', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'confirm_emergency' => ['required', 'accepted'],
        ]);

        $membership = DB::table('resident_homes')
            ->where('id', $data['resident_home_id'])
            ->where('user_id', $request->user()->id)->first();
        abort_unless($membership, 404, 'Resident home not found.');

        $status = DB::table('emergency_request_statuses')->where('code', 'received')->first();
        abort_unless($status, 500, 'Emergency request statuses have not been seeded.');

        $id = DB::transaction(function () use ($data, $membership, $request, $status) {
            $id = DB::table('emergency_requests')->insertGetId([
                'resident_id' => $request->user()->id,
                'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id,
                'emergency_type_id' => $data['emergency_type_id'],
                'status_id' => $status->id,
                'reference' => 'EM-' . Str::upper(Str::random(12)),
                'contact_phone' => $data['contact_phone'],
                'location_details' => $data['location_details'],
                'description' => $data['description'],
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('emergency_request_updates')->insert([
                'emergency_request_id' => $id,
                'status_id' => $status->id,
                'updated_by' => $request->user()->id,
                'note' => null,
                'created_at' => now(),
            ]);

            return $id;
        });

        return response()->json([
            'data' => DB::table('emergency_requests')->where('id', $id)->first(),
            'message' => __('Emergency request received'),
        ], 201);
    }

    public function cancel(Request $request, int $requestId)
    {
        $emergency = DB::table('emergency_requests')
            ->where('id', $requestId)
            ->where('resident_id', $request->user()->id)->first();
        abort_unless($emergency, 404);

        $cancelled = DB::table('emergency_request_statuses')->where('code', 'cancelled')->first();
        abort_unless($cancelled, 500, 'Emergency request statuses have not been seeded.');
        $received = DB::table('emergency_request_statuses')->where('id', $emergency->status_id)->value('code');
        abort_unless($received === 'received', 409, 'A dispatched emergency request cannot be cancelled by the resident.');

        DB::transaction(function () use ($emergency, $cancelled, $request) {
            DB::table('emergency_requests')->where('id', $emergency->id)->update([
                'status_id' => $cancelled->id,
                'updated_at' => now(),
            ]);
            DB::table('emergency_request_updates')->insert([
                'emergency_request_id' => $emergency->id,
                'status_id' => $cancelled->id,
                'updated_by' => $request->user()->id,
                'note' => 'Cancelled by resident',
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => __('Emergency request cancelled')]);
    }
}
