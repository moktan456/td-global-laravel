<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Support\Search;
use App\Support\Statuses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $stage = $request->query('status');
        $status = is_string($stage) && Statuses::exists($stage) ? $stage : '';
        $query = Search::text($request->query('q'));
        $like = Search::like(...);

        // Every word must match the tracking number (with or without dashes), the goods, or the client's name, company, email or phone.
        $matchesSearch = function ($qb) use ($query, $like) {
            if ($query === '') {
                return $qb;
            }
            foreach (Search::words($query) as $term) {
                $qb->where(function ($w) use ($term, $like) {
                    $w->whereRaw("tracking_number LIKE ? ESCAPE '!'", [$like($term)])
                        ->orWhereRaw("REPLACE(tracking_number, '-', '') LIKE ? ESCAPE '!'", [$like(str_replace('-', '', $term))])
                        ->orWhereRaw("goods LIKE ? ESCAPE '!'", [$like($term)])
                        ->orWhereHas('client', function ($c) use ($term, $like) {
                            $c->where(function ($x) use ($term, $like) {
                                $x->whereRaw("full_name LIKE ? ESCAPE '!'", [$like($term)])
                                    ->orWhereRaw("company LIKE ? ESCAPE '!'", [$like($term)])
                                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$like($term)]);
                                $digits = Search::digits($term);
                                if ($digits !== '') {
                                    $x->orWhereRaw("phone LIKE ? ESCAPE '!'", [$like($digits)]);
                                }
                            });
                        });
                });
            }

            return $qb;
        };

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
            'inProgress' => Shipment::where('status', '!=', 'released')->count(),
            'updatedToday' => Shipment::where('updated_at', '>=', now('Asia/Thimphu')->startOfDay()->setTimezone(config('app.timezone')))->count(),
        ]);
    }
}
