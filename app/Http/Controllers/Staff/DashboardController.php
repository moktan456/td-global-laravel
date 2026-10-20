<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Support\Statuses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = Statuses::exists($request->query('status')) ? $request->query('status') : '';
        $q = $request->query('q');
        $query = mb_substr(strtoupper(trim(is_array($q) ? (string) ($q[0] ?? '') : (string) $q)), 0, 20);

        $shipments = Shipment::with('client')
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->when($query, fn ($qb) => $qb->where('tracking_number', 'like', '%'.preg_replace('/[^A-Z0-9-]/', '', $query).'%'))
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return view('staff.dashboard', ['shipments' => $shipments, 'status' => $status, 'query' => $query]);
    }
}
