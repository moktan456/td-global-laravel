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
use App\Support\TransportDetails;
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
        // What to track. The form always sends tracks_chosen; a request without it (an old link, a script) means customs only.
        $chosen = $request->boolean('tracks_chosen');
        $customs = $chosen ? $request->boolean('track_customs') : true;
        $transport = $chosen ? $request->boolean('track_transport') : false;
        $values += ['track_customs' => $customs ? '1' : '', 'track_transport' => $transport ? '1' : ''] + TransportDetails::read($request);
        if (! $customs && ! $transport) {
            $errors['tracks'] = 'Choose what to track: customs clearance, transport, or both.';
        }
        if ($transport) {
            $errors += TransportDetails::errors($values);
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
            'customs_enabled' => $customs, 'transport_enabled' => $transport,
        ] + ($transport ? TransportDetails::attributes($values) : []) + [
            // Anyone who can sign in may look after it; anything else (a stale or made-up id) leaves it unassigned.
            'assigned_to' => $values['assigned_to'] !== '' ? User::where('active', true)->find($values['assigned_to'])?->id : null,
        ]);
        $this->notify->client(
            $client, 'tracking_number', $shipment->id,
            Messages::trackingNumberEmail($client->full_name, $shipment->tracking_number, Messages::about($shipment), $shipment->tracks()),
            Messages::telegramTrackingNumber($client->full_name, $shipment->tracking_number, Messages::about($shipment), $shipment->tracks()),
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
            Messages::trackingNumberEmail($client->full_name, $shipment->tracking_number, $about, $shipment->tracks()),
            Messages::telegramTrackingNumber($client->full_name, $shipment->tracking_number, $about, $shipment->tracks()),
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

        // The Update card has a tab per track; ?track=transport opens the transport one (otherwise the first track that is on).
        $asked = $request->query('track');
        $tab = in_array($asked, Statuses::tracks(), true) ? $asked : ($shipment->customs_enabled ? Statuses::CUSTOMS : Statuses::TRANSPORT);

        return view('staff.shipment-show', [
            's' => $shipment,
            'tab' => $tab,
            'events' => $shipment->events->map(fn ($e) => ['status' => $e->status, 'track' => $e->track, 'at' => $e->created_at->toIso8601String(), 'note' => $e->note])->all(),
            'transportState' => form_state('transport'),
            'tracksState' => form_state('tracks'),
            'detailsState' => form_state('tdetails'),
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
        $quick = $request->input('from') === 'list';   // the "Change stage" mini form on the shipments list: answer with a message on the list
        $back = fn (bool $ok, string $message) => redirect()->back()->with('quick', ['ok' => $ok, 'message' => $message]);
        if ($quick && (! $shipment->customs_enabled || ! Statuses::exists($status))) {
            return $back(false, "Couldn't change {$shipment->tracking_number}: customs tracking is off or the stage isn't valid.");
        }
        if (! $shipment->customs_enabled) {
            return $this->formResponse($request, 'status', 'staff.status-form', FormState::failed('Customs tracking is switched off for this shipment. Switch it on under "What we track" first.'), ['s' => $shipment]);
        }
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

        if ($quick) {
            return $back(true, "{$shipment->tracking_number} is now at ".Statuses::info($status)['label'].".{$told}");
        }

        return $this->formResponse($request, 'status', 'staff.status-form', FormState::success("Status updated.{$told}"), ['s' => $shipment->fresh()]);
    }
}
