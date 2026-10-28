<?php

namespace App\Http\Controllers;

use App\Mail\PlainMessage;
use App\Support\FormState;
use App\Support\NotConnected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Contact and quote forms (ported from app/actions.ts). A submission goes to FORM_WEBHOOK_URL (Formspree, Make,
 * Zapier, a CRM...) when set, otherwise by email through the site's mail settings, when those are configured.
 * With neither, the form says so and offers a pre-filled email instead: it never tells a visitor their message was
 * sent when it wasn't.
 */
class FormController extends Controller
{
    private const EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    public function contact(Request $request)
    {
        $values = $this->fields($request, ['name', 'email', 'message']);
        $errors = [];
        $this->required($values, $errors, 'name', 'enterName');
        $this->email($values, $errors);
        $this->required($values, $errors, 'message', 'enterMessage');

        $state = $errors
            ? FormState::invalid($errors, $values)
            : $this->send('contact', $values, label('messages.contactSuccess', ['name' => $values['name']]));

        return $this->formResponse($request, 'contact', 'forms.contact', $state, ['tone' => $request->input('tone') === 'dark' ? 'dark' : 'light']);
    }

    public function quote(Request $request)
    {
        $values = $this->fields($request, ['name', 'email', 'company', 'direction', 'mode', 'goods', 'details', 'plan']);
        $errors = [];
        $this->required($values, $errors, 'name', 'enterName');
        $this->email($values, $errors);
        if ($values['direction'] === '') {
            $errors['direction'] = label('messages.chooseDirection');
        }
        if ($values['mode'] === '') {
            $errors['mode'] = label('messages.chooseMode');
        }
        $this->required($values, $errors, 'goods', 'enterGoods');

        $state = $errors
            ? FormState::invalid($errors, $values)
            : $this->send('quote', $values, label('messages.quoteSuccess', ['name' => $values['name'], 'email' => $values['email']]));

        return $this->formResponse($request, 'quote', 'forms.quote', $state, ['plan' => $values['plan'] ?: null]);
    }

    protected function fields(Request $request, array $names): array
    {
        $values = [];
        foreach ($names as $n) {
            $values[$n] = trim((string) $request->input($n, ''));
        }

        return $values;
    }

    protected function required(array $values, array &$errors, string $name, string $label): void
    {
        if ($values[$name] === '') {
            $errors[$name] = label("messages.{$label}");
        }
    }

    protected function email(array $values, array &$errors, string $name = 'email'): void
    {
        if ($values[$name] === '') {
            $errors[$name] = label('messages.enterEmail');
        } elseif (! preg_match(self::EMAIL, $values[$name])) {
            $errors[$name] = label('messages.badEmail');
        }
    }

    protected function send(string $form, array $values, string $success): FormState
    {
        try {
            $this->deliver($form, $values);

            return FormState::success($success);
        } catch (NotConnected $e) {
            Log::warning('[forms] '.$e->getMessage());

            return FormState::failed(
                label('messages.notConnected'),
                $values,
                $this->mailto($form, $values),
            );
        } catch (\Throwable $e) {
            Log::error('[forms] delivery failed: '.$e->getMessage());

            return FormState::failed(label('messages.sendFailed'), $values);
        }
    }

    protected function deliver(string $form, array $values): void
    {
        if ($hook = env('FORM_WEBHOOK_URL')) {
            $res = Http::timeout(10)->post($hook, ['form' => $form, 'submittedAt' => now()->toIso8601String()] + $values);
            if (! $res->successful()) {
                throw new \RuntimeException("Webhook responded {$res->status()}");
            }

            return;
        }
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            throw new NotConnected("FORM_WEBHOOK_URL is not set and mail is not configured; \"{$form}\" submission not delivered");
        }
        $subject = $form === 'quote' ? 'Quote request' : 'Website enquiry';
        $body = collect($values)->filter()->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n");
        $message = new PlainMessage($subject, $body);
        if (! empty($values['email'])) {
            $message->replyTo($values['email']);
        }
        Mail::to(site('contact.email'))->send($message);
    }

    protected function mailto(string $form, array $values): string
    {
        $subject = $form === 'quote' ? 'Quote request' : 'Website enquiry';
        $body = collect($values)->filter()->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n");

        return 'mailto:'.site('contact.email').'?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
    }
}
