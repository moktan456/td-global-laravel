<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\Notify;
use App\Support\FormState;
use App\Support\Messages;
use App\Support\Statuses;
use App\Support\TrackingNumber;
use App\Support\TransportDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** The transport side of a shipment: what is tracked, the transport stage (with proof of delivery), and the transport details. */
class TransportController extends Controller
{
    public function __construct(private Notify $notify) {}

    private function find(string $tracking): Shipment
    {
        $shipment = Shipment::with('client')->where('tracking_number', TrackingNumber::normalize($tracking))->first();
        abort_if(! $shipment, 404);

        return $shipment;
    }

    /** Switch customs clearance and transport tracking on or off for one shipment (at least one stays on). */
    public function tracks(Request $request, string $tracking)
    {
        $shipment = $this->find($tracking);
        $customs = $request->boolean('track_customs');
        $transport = $request->boolean('track_transport');

        if (! $customs && ! $transport) {
            return $this->formResponse($request, 'tracks', 'staff.tracks-form', FormState::invalid(
                ['tracks' => 'Keep at least one switched on: customs clearance or transport.'],
                ['track_customs' => $shipment->customs_enabled ? '1' : '', 'track_transport' => $shipment->transport_enabled ? '1' : ''],
            ), ['s' => $shipment]);
        }

        $shipment->forceFill(['customs_enabled' => $customs, 'transport_enabled' => $transport])->save();   // a track switched on starts at its first stage
        $shipment = $shipment->fresh();

        return $this->formResponse($request, 'tracks', 'staff.tracks-form', FormState::success($this->tracksMessage($shipment)), ['s' => $shipment]);
    }

    private function tracksMessage(Shipment $s): string
    {
        $names = array_map(fn ($t) => Statuses::trackLabel($t), $s->tracks());

        return 'Now tracking: '.implode(' and ', $names).'.';
    }

    /** Move the transport track to a new stage, optionally with a note and a message to the client. Delivered records the proof of delivery. */
    public function status(Request $request, string $tracking)
    {
        $shipment = $this->find($tracking);
        $data = ['s' => $shipment];

        if (! $shipment->transport_enabled) {
            return $this->formResponse($request, 'transport', 'staff.transport-status-form', FormState::failed('Transport tracking is switched off for this shipment. Switch it on under "What we track" first.'), $data);
        }

        $status = (string) $request->input('status', '');
        $note = trim((string) $request->input('note', ''));
        $receiver = trim((string) $request->input('delivered_to', ''));
        $when = trim((string) $request->input('delivered_at', ''));
        $values = ['status' => $status, 'note' => $note, 'delivered_to' => $receiver, 'delivered_at' => $when];

        $errors = [];
        if (! Statuses::existsIn(Statuses::TRANSPORT, $status)) {
            $errors['status'] = 'Choose a stage.';
        }
        if ($status === 'delivered') {
            if (mb_strlen($receiver) > 120) {
                $errors['delivered_to'] = 'Use 120 characters or fewer.';
            }
            if ($when !== '' && ! $this->parseWhen($when)) {
                $errors['delivered_at'] = 'Enter the date and time of delivery.';
            }
        }
        if ($errors) {
            return $this->formResponse($request, 'transport', 'staff.transport-status-form', FormState::invalid($errors, $values), $data);
        }

        $delivered = $status === 'delivered';
        $deliveredAt = $delivered ? ($when !== '' ? $this->parseWhen($when) : ($shipment->delivered_at ?? now())) : null;
        // What the client reads for this entry: who received it and when, then any note from the team.
        $summary = $delivered ? 'Delivered'.($receiver !== '' ? " to {$receiver}" : '').' on '.$deliveredAt->copy()->timezone('Asia/Thimphu')->format('j M Y, H:i').'.' : '';
        $eventNote = trim($summary.($summary !== '' && $note !== '' ? ' ' : '').$note);

        $before = $shipment->transport_status;
        $changed = $before !== $status;
        $shipment->transport_status = $status;
        $shipment->delivered_to = $delivered ? ($receiver !== '' ? $receiver : null) : null;       // moving away from Delivered clears the proof
        $shipment->delivered_at = $deliveredAt;
        $shipment->save();                                                                            // the model logs the new stage on the timeline

        if ($changed && $eventNote !== '') {
            $shipment->events()->reorder('id', 'desc')->where('track', Statuses::TRANSPORT)->first()?->update(['note' => $eventNote]);
        } elseif (! $changed && $eventNote !== '') {
            $shipment->logStage($eventNote, Statuses::TRANSPORT);                                    // same stage, just a note or an updated proof
        }

        // "Expected delivery: 28 Oct 2026." goes in the message until it has been delivered.
        $expected = ! $delivered ? $shipment->expected_delivery?->format('j M Y') : null;
        $told = '';
        if ($request->input('notify') === 'on' && $shipment->client) {
            $outcomes = $this->notify->client(
                $shipment->client, 'status_update', $shipment->id,
                Messages::statusUpdateEmail($shipment->client->full_name, $shipment->tracking_number, $status, $eventNote ?: null, Messages::about($shipment), Statuses::TRANSPORT, $expected),
                Messages::telegramStatusUpdate($shipment->client->full_name, $shipment->tracking_number, Statuses::info($status), $eventNote ?: null, Messages::about($shipment), Statuses::TRANSPORT, $expected),
            );
            $told = ' '.Notify::describe($outcomes);
        }

        return $this->formResponse($request, 'transport', 'staff.transport-status-form', FormState::success("Transport updated.{$told}"), ['s' => $shipment->fresh()]);
    }

    /** The optional carrier, vehicle, pickup, destination, expected date and (staff-only) driver details. */
    public function details(Request $request, string $tracking)
    {
        $shipment = $this->find($tracking);
        $values = TransportDetails::read($request);
        $errors = TransportDetails::errors($values);
        if ($errors) {
            return $this->formResponse($request, 'tdetails', 'staff.transport-details-form', FormState::invalid($errors, $values), ['s' => $shipment]);
        }

        $shipment->forceFill(TransportDetails::attributes($values))->save();      // not a stage change: nothing is added to the timeline

        return $this->formResponse($request, 'tdetails', 'staff.transport-details-form', FormState::success('Transport details saved.'), ['s' => $shipment->fresh()]);
    }

    /** "2026-10-28T14:30" (a date-time field, in Bhutan time) to a moment; false if it isn't one. */
    private function parseWhen(string $value): Carbon|false
    {
        foreach (['Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d\TH:i:s'] as $format) {
            try {
                $d = Carbon::createFromFormat($format, $value, 'Asia/Thimphu');
            } catch (\Throwable) {
                continue;
            }
            if ($d->format($format) === $value) {
                return $d->setTimezone(config('app.timezone'));
            }
        }

        return false;
    }
}
