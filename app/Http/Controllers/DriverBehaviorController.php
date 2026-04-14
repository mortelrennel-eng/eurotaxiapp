<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverBehaviorController extends Controller
{
    // ─── Incident Types ────────────────────────────────────────────────────
    public static $incidentTypes = [
        'Coding Violation'      => ['color' => 'red',    'icon' => 'shield-alert'],
        'Late Boundary'         => ['color' => 'orange', 'icon' => 'clock'],
        'Short Boundary'        => ['color' => 'yellow', 'icon' => 'trending-down'],
        'Vehicle Damage'        => ['color' => 'purple', 'icon' => 'car-crash'],
        'Accident'              => ['color' => 'red',    'icon' => 'alert-octagon'],
        'Traffic Violation'     => ['color' => 'orange', 'icon' => 'traffic-cone'],
        'Absent / No Show'      => ['color' => 'gray',   'icon' => 'user-x'],
        'Passenger Complaint'   => ['color' => 'blue',   'icon' => 'message-square-warning'],
        'Speeding'              => ['color' => 'red',    'icon' => 'gauge'],
        'Hard Braking'          => ['color' => 'orange', 'icon' => 'zap'],
        'Other'                 => ['color' => 'gray',   'icon' => 'alert-circle'],
    ];

    // ─── INDEX: Unified Incident + Driver Dashboard ─────────────────────
    public function index(Request $request)
    {
        $search          = $request->input('search', '');
        $type_filter     = $request->input('type', '');
        $severity_filter = $request->input('severity', '');
        $date_from       = $request->input('date_from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $date_to         = $request->input('date_to', now()->timezone('Asia/Manila')->toDateString());
        $tab             = $request->input('tab', 'incidents');
        $page            = max(1, (int) $request->input('page', 1));
        $limit           = 15;
        $offset          = ($page - 1) * $limit;

        // ── Unified incident feed: driver_behavior + coding_violations ──
        $query = DB::table('driver_behavior as db')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'db.driver_id', '=', 'd.id')
            ->select(
                'db.id',
                'db.incident_type',
                'db.severity',
                'db.description',
                'db.timestamp',
                'db.incident_date',
                'db.third_party_name',
                'db.third_party_vehicle',
                'db.own_unit_damage_cost',
                'db.third_party_damage_cost',
                'db.is_driver_fault',
                'db.total_charge_to_driver',
                'db.charge_status',
                'db.video_url',
                'db.created_at',
                'u.plate_number',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name"),
                DB::raw("'manual' as source")
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')))"), 'like', "%{$search}%")
                  ->orWhere('u.plate_number', 'like', "%{$search}%")
                  ->orWhere('db.incident_type', 'like', "%{$search}%")
                  ->orWhere('db.description', 'like', "%{$search}%");
            });
        }

        if (!empty($type_filter)) {
            $query->where('db.incident_type', $type_filter);
        }

        if (!empty($severity_filter)) {
            $query->where('db.severity', $severity_filter);
        }

        if (!empty($date_from)) {
            $query->whereDate('db.timestamp', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('db.timestamp', '<=', $date_to);
        }

        $total     = $query->count();
        $incidents = $query->orderByDesc('db.timestamp')->offset($offset)->limit($limit)->get();

        $pagination = [
            'page'        => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_items' => $total,
            'has_prev'    => $page > 1,
            'has_next'    => $page < ceil($total / $limit),
            'prev_page'   => $page - 1,
            'next_page'   => $page + 1,
        ];

        // ── Summary Stats ──────────────────────────────────────────────
        $stats = $this->getStats($date_from, $date_to);

        // ── Driver Performance Profiles ────────────────────────────────
        $driver_profiles = $this->getDriverProfiles($date_from, $date_to);

        // ── Incentive Eligibility ─────────────────────────────────────
        $incentive_summary = $this->getIncentiveSummary();

        // ── Dropdowns ─────────────────────────────────────────────────
        $drivers = DB::table('drivers')->whereNull('deleted_at')
            ->select('id', DB::raw("TRIM(CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,''))) as full_name"))
            ->orderBy('last_name')->get();

        $units = DB::table('units')->whereNull('deleted_at')->where('status', '!=', 'retired')
            ->select('id', 'plate_number')->orderBy('plate_number')->get();

        return view('driver-behavior.index', compact(
            'incidents', 'search', 'type_filter', 'severity_filter',
            'date_from', 'date_to', 'pagination', 'stats',
            'driver_profiles', 'incentive_summary',
            'drivers', 'units', 'tab'
        ));
    }

    // ─── STORE: Record Incident ──────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'                => 'required|integer',
            'driver_id'              => 'required|integer',
            'incident_type'          => 'required|string',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'third_party_name'       => 'nullable|string',
            'third_party_vehicle'    => 'nullable|string',
            'own_unit_damage_cost'   => 'nullable|numeric|min:0',
            'third_party_damage_cost'=> 'nullable|numeric|min:0',
            'is_driver_fault'        => 'nullable|boolean',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
            'charge_status'          => 'nullable|string',
            'latitude'               => 'nullable|numeric',
            'longitude'              => 'nullable|numeric',
            'video_url'              => 'nullable|string',
        ]);

        $isFault = (bool)($data['is_driver_fault'] ?? false);
        $isAccident = in_array($data['incident_type'], ['Vehicle Damage', 'Accident']);

        // Auto-compute total charge if not manually specified
        if ($isFault && $isAccident) {
            $data['total_charge_to_driver'] = ($data['own_unit_damage_cost'] ?? 0)
                + ($data['third_party_damage_cost'] ?? 0);
        }

        DB::table('driver_behavior')->insert([
            'unit_id'                 => $data['unit_id'],
            'driver_id'               => $data['driver_id'],
            'incident_type'           => $data['incident_type'],
            'severity'                => $data['severity'],
            'description'             => $data['description'],
            'incident_date'           => $data['incident_date'] ?? now()->timezone('Asia/Manila')->toDateString(),
            'third_party_name'        => $data['third_party_name'] ?? null,
            'third_party_vehicle'     => $data['third_party_vehicle'] ?? null,
            'own_unit_damage_cost'    => $data['own_unit_damage_cost'] ?? 0,
            'third_party_damage_cost' => $data['third_party_damage_cost'] ?? 0,
            'is_driver_fault'         => $isFault,
            'total_charge_to_driver'  => $data['total_charge_to_driver'] ?? 0,
            'charge_status'           => ($data['total_charge_to_driver'] ?? 0) > 0 ? 'pending' : 'none',
            'latitude'                => $data['latitude'] ?? 0,
            'longitude'               => $data['longitude'] ?? 0,
            'video_url'               => $data['video_url'] ?? '',
            'timestamp'               => now()->timezone('Asia/Manila'),
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        // If driver at fault → void incentive for any boundary on incident date
        if ($isFault && !empty($data['incident_date'])) {
            DB::table('boundaries')
                ->where('driver_id', $data['driver_id'])
                ->whereDate('date', $data['incident_date'])
                ->update([
                    'has_incentive'          => false,
                    'counted_for_incentive'  => false,
                    'notes'                  => DB::raw("CONCAT(COALESCE(notes,''), ' [Disqualified: Driver at fault in accident]')")
                ]);
        }

        // Create system alert for accidents
        if ($isAccident) {
            $unit      = DB::table('units')->find($data['unit_id']);
            $driver    = DB::table('drivers')->find($data['driver_id']);
            $plateName = $unit->plate_number ?? 'Unknown Unit';
            $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
            DB::table('system_alerts')->insert([
                'title'       => "Accident Reported: {$plateName}",
                'message'     => "Driver {$driverName} reported an accident. Fault: " . ($isFault ? 'YES' : 'NO') . ". Charge: ₱" . number_format($data['total_charge_to_driver'] ?? 0, 2),
                'type'        => 'danger',
                'is_resolved' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident recorded successfully.');
    }

    // ─── DELETE ─────────────────────────────────────────────────────────
    public function destroy($id)
    {
        DB::table('driver_behavior')->where('id', $id)->delete();
        return redirect()->route('driver-behavior.index')->with('success', 'Incident deleted.');
    }

    // ─── RELEASE INCENTIVE ───────────────────────────────────────────────
    public function releaseIncentive(Request $request)
    {
        $driver_id   = $request->input('driver_id');
        $release_date = now()->timezone('Asia/Manila')->toDateString();

        // Mark all unreleased counted boundaries for this driver as released
        DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->update(['incentive_released_at' => $release_date]);

        // Mark all violations for this driver as released
        DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->update(['incentive_released_at' => $release_date]);

        $driver = DB::table('drivers')->find($driver_id);
        $name = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));

        return redirect()->route('driver-behavior.index', ['tab' => 'incentives'])
            ->with('success', "Incentive released for {$name}. Counter reset.");
    }

    // ─── DRIVER PERFORMANCE JSON ─────────────────────────────────────────
    public function getDriverPerformance(Request $request, $driver_id)
    {
        $from = $request->input('from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->timezone('Asia/Manila')->toDateString());

        $driver = DB::table('drivers')->find($driver_id);
        if (!$driver) return response()->json(['error' => 'Driver not found'], 404);

        $unit = DB::table('units')
            ->where(function($q) use ($driver_id) {
                $q->where('driver_id', $driver_id)
                  ->orWhere('secondary_driver_id', $driver_id);
            })
            ->whereNull('deleted_at')
            ->first();

        $incidents = DB::table('driver_behavior as db')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->where('db.driver_id', $driver_id)
            ->whereDate('db.timestamp', '>=', $from)
            ->whereDate('db.timestamp', '<=', $to)
            ->select('db.*', 'u.plate_number')
            ->orderByDesc('db.timestamp')
            ->limit(10)->get();

        $boundaries_count = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->count();

        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        $total_charges = DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->sum('total_charge_to_driver');

        $incentive = $this->computeIncentiveForDriver($driver_id, $unit);

        return response()->json([
            'driver'           => $driver,
            'unit'             => $unit,
            'incidents'        => $incidents,
            'boundaries_count' => $boundaries_count,
            'valid_days'       => $valid_days,
            'total_charges'    => $total_charges,
            'incentive'        => $incentive,
        ]);
    }

    // ─── PRIVATE: Compute Incentive For One Driver ───────────────────────
    private function computeIncentiveForDriver($driver_id, $unit)
    {
        // Determine if solo or dual
        $is_dual = $unit && !empty($unit->secondary_driver_id) && !empty($unit->driver_id);

        // Count unreleased valid boundary days
        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        // Violations this period
        $violations = DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->where(function($q) {
                $q->where('severity', '!=', 'low')
                  ->orWhere('is_driver_fault', true);
            })
            ->count();

        $required_days = 20;
        $eligible = $valid_days >= $required_days && $violations === 0;

        // Compute next payout Sunday
        $now = Carbon::now('Asia/Manila');
        
        // Find the unit ID for staggering dual drivers
        $unitId = $unit->id ?? 0;

        if ($is_dual) {
            // Dual Driver: 2-month cycle staggered by Unit ID.
            // Split dual drivers into 2 groups so they don't all pay on the same month.
            // Group A (Odd Unit ID): Pays in ODD months (Jan, Mar, May, Jul, Sep, Nov)
            // Group B (Even Unit ID): Pays in EVEN months (Feb, Apr, Jun, Aug, Oct, Dec)
            
            $isOddUnit = ($unitId % 2 !== 0);
            $currentMonth = $now->month;
            
            // Determine if THIS month is the payout month for this unit
            $isPayoutMonth = ($isOddUnit && ($currentMonth % 2 !== 0)) || (!$isOddUnit && ($currentMonth % 2 === 0));
            
            if ($isPayoutMonth) {
                // Check if the 1st Sunday of THIS month has already passed
                $firstSunday = $now->copy()->startOfMonth();
                while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
                
                if ($now->gt($firstSunday->endOfDay())) {
                    // Passed -> Next natural payout is in 2 months
                    $targetMonth = $now->copy()->addMonths(2)->startOfMonth();
                } else {
                    // Not passed -> Natural payout is THIS month
                    $targetMonth = $now->copy()->startOfMonth();
                }
            } else {
                // Not the payout month -> Payout is NEXT month
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more 2-month cycle
            if ($violations > 0) {
                $targetMonth->addMonths(2);
            }

        } else {
            // Solo Driver: Strictly 1 month cycle. Payout is every 1st Sunday.
            $firstSunday = $now->copy()->startOfMonth();
            while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
            
            if ($now->gt($firstSunday->endOfDay())) {
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            } else {
                $targetMonth = $now->copy()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more month
            if ($violations > 0) {
                $targetMonth->addMonth();
            }
        }

        // Find the 1st Sunday of the target month
        $payoutDate = $targetMonth->copy();
        while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) {
            $payoutDate->addDay();
        }

        return [
            'is_dual'          => $is_dual,
            'valid_days'       => $valid_days,
            'violations'       => $violations,
            'eligible'         => $eligible,
            'next_payout_date' => $payoutDate->format('M d, Y'),
            'required_days'    => $required_days,
            'driver_type'      => $is_dual ? 'Dual Driver' : 'Solo Driver',
        ];
    }

    // ─── PRIVATE: Summary Stats ─────────────────────────────────────────
    private function getStats($from, $to)
    {
        $base  = DB::table('driver_behavior')->whereDate('timestamp', '>=', $from)->whereDate('timestamp', '<=', $to);
        $bySev = (clone $base)->selectRaw('severity, COUNT(*) as count')->groupBy('severity')->get()->pluck('count', 'severity')->toArray();
        $byType = (clone $base)->selectRaw('incident_type, COUNT(*) as count')->groupBy('incident_type')->orderByDesc('count')->limit(8)->get();

        $totalViolators = DB::table('driver_behavior')->whereDate('timestamp', '>=', $from)
            ->where(function($q) { $q->where('severity', 'high')->orWhere('severity', 'critical')->orWhere('is_driver_fault', true); })
            ->distinct('driver_id')->count('driver_id');

        $totalCharges   = DB::table('driver_behavior')->sum('total_charge_to_driver');
        $pendingCharges = DB::table('driver_behavior')->where('charge_status', 'pending')->sum('total_charge_to_driver');

        return [
            'incidents_period'  => (clone $base)->count(),
            'by_severity'       => $bySev,
            'incident_types'    => $byType,
            'total_violators'   => $totalViolators,
            'total_charges'     => $totalCharges,
            'pending_charges'   => $pendingCharges,
        ];
    }

    // ─── PRIVATE: Driver Profiles ───────────────────────────────────────
    private function getDriverProfiles($from, $to)
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select(
                'd.id', 'd.first_name', 'd.last_name', 'd.driver_status',
                'u.id as unit_id', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id'
            )
            ->distinct('d.id')
            ->get();

        $profiles = [];
        foreach ($drivers as $d) {
            $unit_obj  = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null, 'plate_number' => $d->plate_number ?? null];
            $incentive = $this->computeIncentiveForDriver($d->id, $unit_obj);
            $is_dual   = $incentive['is_dual'];

            // For "Accurate" statistics in the profile card:
            // We only show UNRELEASED stats for the current cycle to match the progress bar.
            
            $incidents = DB::table('driver_behavior')
                ->where('driver_id', $d->id)
                ->whereNull('incentive_released_at')
                ->count();

            $boundaries = DB::table('boundaries')
                ->where('driver_id', $d->id)
                ->whereNull('incentive_released_at')
                ->count();

            $shortages = DB::table('boundaries')
                ->where('driver_id', $d->id)
                ->whereNull('incentive_released_at')
                ->sum('shortage');

            $charges = DB::table('driver_behavior')
                ->where('driver_id', $d->id)
                ->whereNull('incentive_released_at')
                ->sum('total_charge_to_driver');

            $profiles[] = [
                'id'          => $d->id,
                'name'        => trim($d->first_name . ' ' . $d->last_name),
                'status'      => $d->driver_status,
                'unit'        => $d->plate_number,
                'unit_id'     => $d->unit_id,
                'incidents'   => $incidents,
                'boundaries'  => $boundaries,
                'shortages'   => $shortages,
                'charges'     => $charges,
                'incentive'   => $incentive,
            ];
        }

        return collect($profiles)->sortBy('name')->values();
    }

    // ─── PRIVATE: Incentive Summary ─────────────────────────────────────
    private function getIncentiveSummary()
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select('d.id', 'd.first_name', 'd.last_name', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id')
            ->distinct('d.id')->get();

        $eligible   = [];
        $ineligible = [];

        foreach ($drivers as $d) {
            $unit = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null];
            $inc  = $this->computeIncentiveForDriver($d->id, $unit);

            $row = [
                'driver_id'     => $d->id,
                'name'          => trim($d->first_name . ' ' . $d->last_name),
                'unit'          => $d->plate_number,
                'driver_type'   => $inc['driver_type'],
                'valid_days'    => $inc['valid_days'],
                'violations'    => $inc['violations'],
                'eligible'      => $inc['eligible'],
                'next_payout'   => $inc['next_payout_date'],
            ];

            if ($inc['eligible']) {
                $eligible[] = $row;
            } else {
                $ineligible[] = $row;
            }
        }

        return ['eligible' => $eligible, 'ineligible' => $ineligible];
    }

    public function getStatistics(Request $request)
    {
        // Kept for backward compatibility
        return response()->json($this->getStats(now()->subDays(30)->toDateString(), now()->toDateString()));
    }
}
