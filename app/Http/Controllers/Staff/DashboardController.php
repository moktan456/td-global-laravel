<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Support\ListFilters;
use App\Support\Search;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $status = ListFilters::stage($request->query('status'));
        $query = Search::text($request->query('q'));
        $matchesSearch = fn ($qb) => ListFilters::shipments($qb, $query);

        $shipments = Shipment::with('client')
            ->tap($matchesSearch)
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($shipments->isEmpty() && $shipments->currentPage() > 1) {   // e.g. a bookmarked page that no longer exists
            return redirect()->to($shipments->url(max(1, $shipments->lastPage())));
        }

        // How many shipments sit at each stage, within the current search (the stage chips above the list).
        $perStage = Shipment::query()->tap($matchesSearch)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status')->all();

        return view('staff.dashboard', [
            'shipments' => $shipments,
            'status' => $status,
            'query' => $query,
            'perStage' => $perStage,
            'hasAny' => Shipment::query()->exists(),
            'inProgress' => Shipment::where('status', '!=', 'released')->count(),
            'updatedToday' => Shipment::where('updated_at', '>=', now('Asia/Thimphu')->startOfDay()->setTimezone(config('app.timezone')))->count(),
        ]);
    }
}
