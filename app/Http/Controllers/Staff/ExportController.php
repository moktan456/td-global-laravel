<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Shipment;
use App\Support\Csv;
use App\Support\ListFilters;
use App\Support\Search;
use App\Support\Statuses;
use Illuminate\Http\Request;

/** CSV of exactly what a staff list is showing: the same search and filter, every matching row (not only the visible page). */
class ExportController extends Controller
{
    private function when(mixed $moment): string
    {
        return $moment ? $moment->timezone('Asia/Thimphu')->format('Y-m-d H:i') : '';
    }

    public function shipments(Request $request)
    {
        $status = ListFilters::stage($request->query('status'));
        $query = Shipment::query()->with(['client', 'assignee'])
            ->tap(fn ($qb) => ListFilters::assignedTo(
                ListFilters::shipments($qb, Search::text($request->query('q'))),
                ListFilters::assignee($request->query('assigned')),
                $request->user()->id,
            ))
            ->when($status !== '', fn ($qb) => $qb->where('status', $status))
            ->orderByDesc('updated_at')->orderByDesc('id');
        $methods = array_column(content('statuses.modes'), 'label', 'value');

        return Csv::download('shipments', [
            'Tracking number', 'Client', 'Company', 'Email', 'Phone', 'Direction', 'Method', 'Goods', 'Stage', 'Requested by', 'Created', 'Last updated', 'Notes on the request', 'Assigned to',
        ], function (callable $write) use ($query, $methods) {
            $query->chunk(500, function ($shipments) use ($write, $methods) {
                foreach ($shipments as $s) {
                    $write([
                        $s->tracking_number, $s->client?->full_name, $s->client?->company, $s->client?->email, $s->client?->phone,
                        ucfirst($s->direction), $methods[$s->mode] ?? $s->mode, $s->goods, Statuses::info($s->primaryStatus())['label'],
                        $s->requested_by === 'staff' ? 'Staff' : 'Client', $this->when($s->created_at), $this->when($s->updated_at), $s->details, $s->assignee?->name,
                    ]);
                }
            });
        });
    }

    public function clients(Request $request)
    {
        $query = Client::query()
            ->tap(fn ($qb) => ListFilters::clients($qb, Search::text($request->query('q')), ListFilters::telegram($request->query('telegram'))))
            ->withCount(['shipments as requests_count', 'shipments as in_progress_count' => fn ($q) => $q->inProgress()])
            ->orderByDesc('created_at')->orderByDesc('id');

        return Csv::download('clients', [
            'Name', 'Company', 'Email', 'Phone', 'Telegram', 'Email updates', 'Registered', 'Registered by', 'Requests', 'In progress',
        ], function (callable $write) use ($query) {
            $query->chunk(500, function ($clients) use ($write) {
                foreach ($clients as $c) {
                    $write([
                        $c->full_name, $c->company, $c->email, $c->phone,
                        $c->telegram_chat_id ? ($c->notify_telegram ? 'Connected' : 'Connected, off') : 'Not connected',
                        $c->notify_email ? 'On' : 'Off', $this->when($c->created_at), $c->registered_by === 'staff' ? 'Staff' : 'Client',
                        $c->requests_count, $c->in_progress_count,
                    ]);
                }
            });
        });
    }
}
