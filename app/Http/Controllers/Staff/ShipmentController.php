<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\NotificationLog;
use App\Models\Shipment;
use App\Services\Notify;
use App\Support\FormState;
use App\Support\Messages;
use App\Support\Statuses;
use App\Support\TrackingNumber;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(private Notify $notify) {}

    public function create()
    {
        $clients = Client::orderBy('full_name')->get()->map(fn ($c) => [
            'value' => (string) $c->id,
            'label' => $c->full_name.($c->company ? " ({$c->company})" : '')." · {$c->phone}",
        ])->all();

        return view('staff.shipment-new', ['clients' => $clients, 'state' => form_state('shipment')]);
    }

    /** Staff create a clearance request on a client's behalf. */
    public function store(Request $request)
    {
        $values = collect(['client_id', 'direction', 'mode', 'goods', 'details'])->mapWithKeys(fn ($k) => [$k => trim((string) $request->input($k, ''))])->all();
        $errors = [];
        if ($values['client_id'] === '') {
            $errors['client_id'] = 'Choose the client.';
        }
        if (! in_array($values['direction'], ['import', 'export'], true)) {
            $errors['direction'] = 'Choose import or export.';
        }
        if (! in_array($values['mode'], ['air', 'road', 'rail'], true)) {
            $errors['mode'] = 'Choose how the goods are shipped.';
        }
        if ($values['goods'] === '') {
            $errors['goods'] = 'Enter a short description of the goods.';
        }
        $client = $values['client_id'] !== '' ? Client::find($values['client_id']) : null;
        if (! $errors && ! $client) {
            $errors['client_id'] = 'Choose the client.';
        }
        $clients = fn () => Client::orderBy('full_name')->get()->map(fn ($c) => ['value' => (string) $c->id, 'label' => $c->full_name.($c->company ? " ({$c->company})" : '')." · {$c->phone}"])->all();
        if ($errors) {
            return $this->formResponse($request, 'shipment', 'staff.shipment-form', FormState::invalid($errors, $values), ['clients' => $clients()]);
        }

        $shipment = Shipment::create([
            'client_id' => $client->id, 'direction' => $values['direction'], 'mode' => $values['mode'],
            'goods' => $values['goods'], 'details' => $values['details'] ?: null,
            'requested_by' => 'staff', 'created_by' => $request->user()->id,
        ]);
        $this->notify->client(
            $client, 'tracking_number', $shipment->id,
            Messages::trackingNumberEmail($client->full_name, $shipment->tracking_number, Messages::about($shipment)),
            Messages::telegramTrackingNumber($client->full_name, $shipment->tracking_number, Messages::about($shipment)),
        );

        return $this->formRedirect($request, "/staff/shipments/{$shipment->tracking_number}?new=1");
    }

    public function show(Request $request, string $tracking)
    {
        $shipment = Shipment::with('client')->where('tracking_number', TrackingNumber::normalize($tracking))->first();
        abort_if(! $shipment, 404);

        return view('staff.shipment-show', [
            's' => $shipment,
            'events' => $shipment->events->map(fn ($e) => ['status' => $e->status, 'at' => $e->created_at->toIso8601String(), 'note' => $e->note])->all(),
            'log' => NotificationLog::where('shipment_id', $shipment->id)->orderByDesc('created_at')->orderByDesc('id')->limit(30)->get(),
            'isNew' => $request->query('new') !== null,
            'state' => form_state('status'),
        ]);
    }

    /** Move a shipment to a new stage, optionally with a note and a message to the client. */
    public function updateStatus(Request $request, string $tracking)
    {
        $shipment = Shipment::with('client')->where('tracking_number', TrackingNumber::normalize($tracking))->first();
        abort_if(! $shipment, 404);
        $status = (string) $request->input('status', '');
        $note = trim((string) $request->input('note', ''));
        $values = ['status' => $status, 'note' => $note];
        if (! Statuses::exists($status)) {
            return $this->formResponse($request, 'status', 'staff.status-form', FormState::invalid(['status' => 'Choose a stage.'], $values), ['s' => $shipment]);
        }

        $before = $shipment->status;
        if ($before !== $status) {
            $shipment->status = $status;
            $shipment->save();                              // the model logs the new stage on the timeline
            if ($note !== '') {
                $shipment->events()->reorder('id', 'desc')->first()?->update(['note' => $note]);
            }
        } elseif ($note !== '') {
            $shipment->logStage($note);                     // same stage, just a note: its own timeline entry
        }

        $told = '';
        if ($request->input('notify') === 'on' && $shipment->client) {
            $outcomes = $this->notify->client(
                $shipment->client, 'status_update', $shipment->id,
                Messages::statusUpdateEmail($shipment->client->full_name, $shipment->tracking_number, $status, $note ?: null, Messages::about($shipment)),
                Messages::telegramStatusUpdate($shipment->client->full_name, $shipment->tracking_number, Statuses::info($status), $note ?: null, Messages::about($shipment)),
            );
            $told = ' '.Notify::describe($outcomes);
        }

        return $this->formResponse($request, 'status', 'staff.status-form', FormState::success("Status updated.{$told}"), ['s' => $shipment->fresh()]);
    }
}
