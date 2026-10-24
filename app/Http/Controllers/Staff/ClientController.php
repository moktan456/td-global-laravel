<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\TelegramLinkToken;
use App\Services\Mailer;
use App\Services\Telegram;
use App\Support\FormState;
use App\Support\ListFilters;
use App\Support\Messages;
use App\Support\Search;
use App\Support\TrackingNumber;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private const PER_PAGE = 10;

    /** The client list: search by name, company, email or phone; optionally only those with (or without) Telegram; ten a page. */
    public function index(Request $request)
    {
        $q = Search::text($request->query('q'));
        $telegramFilter = ListFilters::telegram($request->query('telegram'));

        $clients = Client::query()
            ->tap(fn ($qb) => ListFilters::clients($qb, $q, $telegramFilter))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($clients->isEmpty() && $clients->currentPage() > 1) {   // e.g. a bookmarked page that no longer exists
            return redirect()->to($clients->url(max(1, $clients->lastPage())));
        }

        return view('staff.clients', [
            'clients' => $clients,
            'total' => Client::count(),
            'q' => $q,
            'telegramFilter' => $telegramFilter,
            'state' => form_state('client'),
            'telegram' => Telegram::configured(),
        ]);
    }

    /** One client: their requests (the default tab), their Telegram connection, and their details. */
    public function show(Request $request, Client $client)
    {
        $telegram = Telegram::configured();
        $tab = $request->query('tab');
        $tab = in_array($tab, ['requests', 'telegram', 'details'], true) && ($tab !== 'telegram' || $telegram) ? $tab : 'requests';

        $shipments = $tab === 'requests'
            ? Shipment::where('client_id', $client->id)->orderByDesc('updated_at')->orderByDesc('id')->paginate(15)->withQueryString()
            : null;

        return view('staff.client-show', [
            'client' => $client,
            'tab' => $tab,
            'shipments' => $shipments,
            'telegram' => $telegram,
            'requestCount' => $shipments?->total() ?? Shipment::where('client_id', $client->id)->count(),
            'inProgress' => Shipment::where('client_id', $client->id)->where('status', '!=', 'released')->count(),
        ]);
    }

    /** Staff register a client on their behalf (e.g. after a phone call). */
    public function store(Request $request, Mailer $mailer)
    {
        $values = collect(['full_name', 'company', 'email', 'phone', 'notify'])->mapWithKeys(fn ($k) => [$k => trim((string) $request->input($k, ''))])->all();
        $errors = [];
        if ($values['full_name'] === '') {
            $errors['full_name'] = "Enter the client's name.";
        }
        if ($values['email'] === '') {
            $errors['email'] = 'Enter your email address.';
        } elseif (! preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $values['email'])) {
            $errors['email'] = 'Enter an email address in the format name@example.com.';
        }
        $phone = TrackingNumber::normalizePhone($values['phone']);
        if (! preg_match('/^\+\d{8,15}$/', $phone)) {
            $errors['phone'] = "Enter the client's phone number, for example 17975757.";
        }
        if ($errors) {
            return $this->formResponse($request, 'client', 'staff.client-form', FormState::invalid($errors, $values));
        }
        if (Client::where('email', mb_strtolower($values['email']))->orWhere('phone', $phone)->exists()) {
            return $this->formResponse($request, 'client', 'staff.client-form', FormState::failed('A client with this email or phone already exists.', $values));
        }

        Client::create([
            'full_name' => $values['full_name'], 'company' => $values['company'] ?: null, 'email' => mb_strtolower($values['email']),
            'phone' => $phone, 'registered_by' => 'staff', 'created_by' => $request->user()->id,
        ]);
        if ($values['notify'] === 'on') {
            $mail = Messages::accountReadyEmail($values['full_name']);
            $mailer->deliver($values['email'], $mail['subject'], $mail['text']);
        }

        return $this->formResponse($request, 'client', 'staff.client-form', FormState::success("{$values['full_name']} is registered."));
    }

    /** Staff create a one-time Telegram connect link for a client (e.g. to send over WhatsApp after a phone call). */
    public function telegramLink(Request $request, Client $client)
    {
        $data = ['client' => $client];
        if (! Telegram::configured()) {
            return $this->formResponse($request, "tglink{$client->id}", 'staff.telegram-link-form', FormState::failed("Telegram isn't set up yet."), $data);
        }
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');   // 43 URL-safe characters
        // Tidy up: old links are of no use to anyone, and a fresh link replaces this client's earlier unused ones.
        TelegramLinkToken::where('expires_at', '<', now()->subDays(7))->delete();
        TelegramLinkToken::where('client_id', $client->id)->whereNull('used_at')->delete();
        TelegramLinkToken::create([
            'token_hash' => hash('sha256', $token),
            'client_id' => $client->id,
            'created_by' => $request->user()->id,
            'expires_at' => now()->addHours(config('td.telegram.link_ttl_hours')),
        ]);
        $link = Telegram::connectLink($token);
        $state = FormState::success('Link ready. It works once and expires in 48 hours. Send it only to this client. Any earlier link for this client no longer works.');
        $state->values = ['link' => $link, 'wa' => Messages::waLink($client->phone, Messages::whatsappTelegramInvite($client->full_name, $link)) ?? ''];

        return $this->formResponse($request, "tglink{$client->id}", 'staff.telegram-link-form', $state, $data);
    }
}
