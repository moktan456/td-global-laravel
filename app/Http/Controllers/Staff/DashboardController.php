<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Support\Assignees;
use App\Support\ListFilters;
use App\Support\Search;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const PER_PAGE = 15;

    /** The "Find a shipment" box: an exact tracking number opens that shipment, anything else becomes a search. */
    public function go(Request $request)
    {
        $typed = Search::text($request->query('n'), 40);
        $wanted = strtoupper(str_replace(['-', ' '], '', $typed));
        if ($wanted !== '') {
            $match = Shipment::query()->whereRaw("REPLACE(tracking_number, '-', '') = ?", [$wanted])->value('tracking_number');
            if (! $match && preg_match('/^[A-Z0-9]{5,}$/', $wanted)) {   // just the last characters (the 6-character code): fine if only one shipment ends that way
                $ends = Shipment::query()->whereRaw("REPLACE(tracking_number, '-', '') LIKE ?", ['%'.$wanted])->limit(2)->pluck('tracking_number');
                $match = $ends->count() === 1 ? $ends->first() : null;
            }
            if ($match) {
                return redirect("/staff/shipments/{$match}");
            }
        }

        return redirect('/staff'.($typed !== '' ? '?'.http_build_query(['q' => $typed]) : ''));
    }

    public function index(Request $request)
    {
        $status = ListFilters::stage($request->query('status'));
        $query = Search::text($request->query('q'));
        $assigned = ListFilters::assignee($request->query('assigned'));
        $sort = ListFilters::sort($request->query('sort'));
        $me = $request->user()->id;
        // The search and the "assigned to" choice apply to the list and to the stage counts alike.
        $matchesSearch = fn ($qb) => ListFilters::assignedTo(ListFilters::shipments($qb, $query), $assigned, $me);

        $shipments = Shipment::with(['client', 'assignee'])
            ->tap($matchesSearch)
            ->tap(fn ($qb) => $status !== '' ? ListFilters::atStage($qb, $status) : $qb)
            ->tap(fn ($qb) => ListFilters::sortShipments($qb, $sort))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($shipments->isEmpty() && $shipments->currentPage() > 1) {   // e.g. a bookmarked page that no longer exists
            return redirect()->to($shipments->url(max(1, $shipments->lastPage())));
        }

        // How many shipments sit at each stage of each track, within the current search (the stage chips above the list).
        // A stage key belongs to one track, so the two lots of counts share one map.
        $perStage = Shipment::query()->tap($matchesSearch)->where('customs_enabled', true)->selectRaw('status as stage, COUNT(*) as n')->groupBy('status')->pluck('n', 'stage')->all()
            + Shipment::query()->tap($matchesSearch)->where('transport_enabled', true)->selectRaw('transport_status as stage, COUNT(*) as n')->groupBy('transport_status')->pluck('n', 'stage')->all();

        return view('staff.dashboard', [
            'shipments' => $shipments,
            'status' => $status,
            'query' => $query,
            'assigned' => $assigned,
            'sort' => $sort,
            'people' => Assignees::options(),
            'mine' => Shipment::where('assigned_to', $me)->inProgress()->count(),
            'perStage' => $perStage,
            'everything' => Shipment::query()->tap($matchesSearch)->count(),
            // The transport chips only appear once someone actually uses transport tracking.
            'usesTransport' => Shipment::where('transport_enabled', true)->exists(),
            'hasAny' => Shipment::query()->exists(),
            'inProgress' => Shipment::inProgress()->count(),
            'updatedToday' => Shipment::where('updated_at', '>=', now('Asia/Thimphu')->startOfDay()->setTimezone(config('app.timezone')))->count(),
        ]);
    }
}
