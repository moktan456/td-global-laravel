<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shipment;
use App\Services\Notify;
use App\Support\FormState;
use App\Support\Messages;
use App\Support\Statuses;
use App\Support\TrackingNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Public tracking (ported from src/app/track/actions.ts): the tracking number alone is enough, nothing about the
 * client is revealed, and "lost your number?" sends the numbers to the contact details already on file.
 */
class TrackController extends Controller
{
    public function __construct(private Notify $notify) {}

    /** The Track page. A link from an email or message (/track?n=TD-2610-K7QX4M) shows the status straight away. */
    public function show(Request $request)
    {
        $linked = is_string($request->query('n')) ? mb_substr($request->query('n'), 0, 40) : '';
        $state = form_state('track');
        if ($linked !== '' && $state->status === 'idle') {
            $state = $this->lookupState($request, $linked);
        }

        return view('pages.track', ['state' => $state, 'reminderState' => form_state('reminder')]);
    }

    public function lookup(Request $request)
    {
        $state = $this->lookupState($request, trim((string) $request->input('tracking', '')));

        return $this->formResponse($request, 'track', 'track.lookup', $state);
    }

    private function lookupState(Request $request, string $raw): FormState
    {
        $found = $this->find($request, $raw);
        if (isset($found['error'])) {
            return FormState::failed($found['error'], ['tracking' => $found['number'] ?: $raw]);
        }
        $state = FormState::success("Status for {$found['number']}");
        $state->values = ['tracking' => $found['number']];
        $state->result = $found['result'];

        return $state;
    }

    /**
     * Looks one tracking number up. The number is the only key, so each visitor gets a small allowance of lookups,
     * and the same message is used for "not found" however the number is wrong.
     *
     * @return array{number: string, error?: string, result?: array}
     */
    private function find(Request $request, string $raw): array
    {
        $number = TrackingNumber::normalize($raw);
        if (! TrackingNumber::isValid($number)) {
            return ['number' => $number, 'error' => 'Enter a tracking number like TD-2610-K7QX4M.'];
        }
        $limit = config('td.limits.track');
        $key = 'track:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
            return ['number' => $number, 'error' => 'Too many lookups from this connection. Please wait a few minutes and try again.'];
        }
        RateLimiter::hit($key, $limit['seconds']);

        $shipment = Shipment::where('tracking_number', $number)->first();
        if (! $shipment) {
            return ['number' => $number, 'error' => "We couldn't find a shipment with that tracking number. Check it and try again, or ask us to resend it below."];
        }

        $events = $shipment->events()->where('visible_to_client', true)->get();

        return ['number' => $number, 'result' => [
            'tracking_number' => $shipment->tracking_number,
            'status' => $shipment->primaryStatus(),
            'updated_at' => $shipment->updated_at->toIso8601String(),
            // One entry per track the shipment follows (customs first): where it is, how far along, and its own timeline.
            'tracks' => collect($shipment->tracks())->map(function (string $track) use ($shipment, $events) {
                $stage = $shipment->stageOn($track);
                [$position, $total] = Statuses::position($stage);

                return [
                    'track' => $track,
                    'label' => Statuses::trackLabel($track),
                    'status' => $stage,
                    'position' => $position,
                    'total' => $total,
                    'finished' => $stage === Statuses::last($track),
                    'events' => $events->where('track', $track)->map(fn ($e) => [
                        'status' => $e->status, 'track' => $e->track, 'at' => $e->created_at->toIso8601String(), 'note' => $e->note,
                    ])->values()->all(),
                ];
            })->all(),
            'delivery' => $this->deliveryDetails($shipment),
        ]];
    }

    /**
     * What a client may see about the transport leg: carrier, vehicle, where from and to, and when to expect it.
     * The driver's name and phone are for staff only and are never added here.
     *
     * @return array<string, string>
     */
    private function deliveryDetails(Shipment $shipment): array
    {
        if (! $shipment->transport_enabled) {
            return [];
        }
        $delivered = $shipment->transport_status === Statuses::last(Statuses::TRANSPORT);

        return array_filter([
            'Carrier' => $shipment->carrier,
            'Vehicle' => $shipment->vehicle_number,
            'From' => $shipment->pickup_place,
            'To' => $shipment->destination,
            'Expected delivery' => $delivered ? null : $shipment->expected_delivery?->format('j M Y'),
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * "I lost my tracking number": the person enters the phone number or email they gave us, and we send their tracking
     * numbers to the contact details we already hold. Nothing is shown on screen, and the answer is the same whether or
     * not a client matched, so this can't be used to find out who is a client.
     */
    public function remind(Request $request)
    {
        $contactRaw = trim((string) $request->input('contact', ''));
        $isEmail = str_contains($contactRaw, '@');
        $phone = $isEmail ? '' : TrackingNumber::normalizePhone($contactRaw);
        $values = ['contact' => $contactRaw];

        $errors = [];
        if ($contactRaw === '') {
            $errors['contact'] = 'Enter the phone number or email you gave us.';
        } elseif ($isEmail ? ! preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $contactRaw) : ! preg_match('/^\+\d{8,15}$/', $phone)) {
            $errors['contact'] = 'Enter a phone number like 17975757, or an email like name@example.com.';
        }
        if ($errors) {
            return $this->formResponse($request, 'reminder', 'track.reminder', new FormState('error', 'Please check the highlighted field.', $errors, $values));
        }

        $contact = $isEmail ? mb_strtolower($contactRaw) : $phone;
        $lv = config('td.limits.remind_visitor');
        $lc = config('td.limits.remind_contact');
        $visitorKey = 'remind-visitor:'.$request->ip();
        $contactKey = 'remind-contact:'.sha1($contact);
        if (RateLimiter::tooManyAttempts($visitorKey, $lv['max']) || RateLimiter::tooManyAttempts($contactKey, $lc['max'])) {
            return $this->formResponse($request, 'reminder', 'track.reminder', FormState::failed("We've sent a few of these already. Please wait an hour, or contact us."));
        }
        RateLimiter::hit($visitorKey, $lv['seconds']);
        RateLimiter::hit($contactKey, $lc['seconds']);

        // Sent after the response, so the page takes the same time whether or not anyone matched.
        defer(fn () => $this->sendReminder($contact, $isEmail));

        return $this->formResponse($request, 'reminder', 'track.reminder', FormState::success(
            "Done. If that phone number or email belongs to one of our clients, we've sent their tracking numbers to the contact details we have on file (email, and Telegram if connected). Nothing there in a few minutes? Please contact us."
        ));
    }

    private function sendReminder(string $contact, bool $isEmail): void
    {
        $client = Client::where($isEmail ? 'email' : 'phone', $contact)->first();
        if (! $client) {
            return;
        }
        $shipments = $client->shipments()->latest()->limit(5)->get();
        if ($shipments->isEmpty()) {
            return;
        }
        $items = $shipments->map(fn ($s) => ['tracking' => $s->tracking_number, 'stage' => $s->stageSummary()])->all();
        // The person asked for this just now, so it is sent even if they had switched notifications off.
        $client->notify_email = true;
        $client->notify_telegram = true;
        $this->notify->client(
            $client,
            'tracking_reminder',
            null,
            Messages::trackingReminderEmail($client->full_name, $items),
            Messages::telegramTrackingReminder($client->full_name, $items),
        );
    }
}
