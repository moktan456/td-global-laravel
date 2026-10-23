<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\TelegramLinkToken;
use App\Services\Mailer;
use App\Services\Telegram;
use App\Support\FormState;
use App\Support\Messages;
use App\Support\TrackingNumber;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('staff.clients', [
            'clients' => Client::orderByDesc('created_at')->limit(200)->get(),
            'state' => form_state('client'),
            'telegram' => Telegram::configured(),
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
