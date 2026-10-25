<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\NotificationLog;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Notify;
use App\Support\Assignees;
use App\Support\FormState;
use App\Support\Messages;
use App\Support\Statuses;
use App\Support\TrackingNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ShipmentController extends Controller
{
    public function __construct(private Notify $notify) {}

    public function create(Request $request)
    {
        $clients = Client::orderBy('full_name')->get()->map(fn ($c) => [
            'value' => (string) $c->id,
            'label' => $c->full_name.($c->company ? " ({$c->company})" : '')." · {$c->phone}",
        ])->all();

        $chosen = $request->query('client');   // /staff/shipments/new?client=12 (from a client's page) starts with them chosen

        return view('staff.shipment-new', ['assignees' => Assignees::options(), 'clients' => $clients, 'state' => form_state('shipment'), 'chosenClient' => is_string($chosen) && ctype_digit($chosen) ? $chosen : null]);
    }

    /** Staff create a clearance request on a client's behalf. */
    public function store(Request $request)
    {
        $values = collect(['client_id', 'direction', 'mode', 'goods', 'details', 'assigned_to'])->mapWithKeys(fn ($k) => [$k => trim((string) $request->input($k, ''))])->all();
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
            return $this->formResponse($request, 'shipment', 'staff.shipment-form', FormState::invalid($errors, $values), ['clients' => $clients(), 'assignees' => Assignees::options()]);
        }

        $shipment = Shipment::create([
            'client_id' => $client->id, 'direction' => $values['direction'], 'mode' => $values['mode'],
            'goods' => $values['goods'], 'details' => $values['details'] ?: null,
            'requested_by' => 'staff', 'created_by' => $request->user()->id,
            // Anyone who can sign in may look after it; anything else (a stale or made-up id) leaves it unassigned.
            'assigned_to' => $values['assigned_to'] !== '' ? User::where('active', true)->find($values['assigned_to'])?->id : null,
        ]);
        $this->notify->client(
            $client, 'tracking_number', $shipment->id,
            Messages::trackingNumberEmail($client->full_name, $shipment->tracking_number, Messages::about($shipment)),
            Messages::telegramTrackingNumber($client->full_name, $shipment->tracking_number, Messages::about($shipment)),
        );

        return $this->formRedirect($request, "/staff/shipments/{$shipment->tracking_number}?new=1");
    }

    /** Send the tracking number to the client again (email and Telegram, following their settings). */
    public function resendTracking(Request $request, string $tracking)
    {
        $shipment = Shipment::with('client')->where('tracking_number', TrackingNumber::normalize($tracking))->first();
        abort_if(! $shipment || ! $shipment->client, 404);

        $key = "resend-tracking:{$shipment->id}";
        if (RateLimiter::tooManyAttempts($key, 6)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

            return $this->formResponse($request, 'resend', 'staff.resend-form', FormState::failed("Already sent several times. Try again in {$minutes} minutes, or copy the link above and send it yourself."), ['s' => $shipment]);
        }
        RateLimiter::hit($key, 3600);

        $client = $shipment->client;
        $about = Messages::about($shipment);
        $outcomes = $this->notify->client(
            $client, 'tracking_number', $shipment->id,
            Messages::trackingNumberEmail($client->full_name, $shipment->tracking_number, $about),
            Messages::telegramTrackingNumber($client->full_name, $shipment->tracking_number, $about),
        );
        $described = Notify::describe($outcomes);
        $state = collect($outcomes)->contains('status', 'sent')
            ? FormState::success("Sent again. {$described}")
            : FormState::failed("Not delivered. {$described} Copy the link above or use WhatsApp to send it yourself.");

        return $this->formResponse($request, 'resend', 'staff.resend-form', $state, ['s' => $shipment]);
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
            'resendState' => form_state('resend'),
            'assignState' => form_state('assign'),
            'assignees' => Assignees::options($shipment->assigned_to),
        ]);
    }

    /** Change who is looking after a shipment (or clear it). */
    public function assign(Request $request, string $tracking)
    {
        $shipment = Shipment::with('client')->where('tracking_number', TrackingNumber::normalize($tracking))->first();
        abort_if(! $shipment, 404);

        $chosen = trim((string) $request->input('assigned_to', ''));
        $person = $chosen !== '' ? User::where('active', true)->find($chosen) : null;
        if ($chosen !== '' && ! $person) {
            return $this->formResponse($request, 'assign', 'staff.assign-form', FormState::invalid(['assigned_to' => 'Choose someone from the list.'], ['assigned_to' => $chosen]), [
                's' => $shipment, 'assignees' => Assignees::options($shipment->assigned_to),
            ]);
        }

        $shipment->forceFill(['assigned_to' => $person?->id])->save();   // not a stage change, so nothing is added to the timeline

        return $this->formResponse($request, 'assign', 'staff.assign-form', FormState::success($person ? "Assigned to {$person->name}." : 'Now unassigned.'), [
            's' => $shipment->fresh(), 'assignees' => Assignees::options($person?->id),
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
                $shipment->events()->reorder('id', 'desc')->where('track', Statuses::CUSTOMS)->first()?->update(['note' => $note]);
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
