<?php

namespace App\Http\Controllers;

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
        $this->required($values, $errors, 'name', 'your name');
        $this->email($values, $errors);
        $this->required($values, $errors, 'message', 'your message');

        $state = $errors
            ? FormState::invalid($errors, $values)
            : $this->send('contact', $values, "Thanks, {$values['name']}. We've received your message and will reply by email.");

        return $this->formResponse($request, 'contact', 'forms.contact', $state, ['tone' => $request->input('tone') === 'dark' ? 'dark' : 'light']);
    }

    public function quote(Request $request)
    {
        $values = $this->fields($request, ['name', 'email', 'company', 'direction', 'mode', 'goods', 'details', 'plan']);
        $errors = [];
        $this->required($values, $errors, 'name', 'your name');
        $this->email($values, $errors);
        if ($values['direction'] === '') {
            $errors['direction'] = 'Choose import or export.';
        }
        if ($values['mode'] === '') {
            $errors['mode'] = 'Choose how the goods are shipped.';
        }
        $this->required($values, $errors, 'goods', 'a short description of the goods');

        $state = $errors
            ? FormState::invalid($errors, $values)
            : $this->send('quote', $values, "Thanks, {$values['name']}. We'll email your quote to {$values['email']}.");

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
            $errors[$name] = "Enter {$label}.";
        }
    }

    protected function email(array $values, array &$errors, string $name = 'email'): void
    {
        if ($values[$name] === '') {
            $errors[$name] = 'Enter your email address.';
        } elseif (! preg_match(self::EMAIL, $values[$name])) {
            $errors[$name] = 'Enter an email address in the format name@example.com.';
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
                "Our online form isn't connected yet — please email us at ".site('contact.email')." instead. We've filled in an email with your details.",
                $values,
                $this->mailto($form, $values),
            );
        } catch (\Throwable $e) {
            Log::error('[forms] delivery failed: '.$e->getMessage());

            return FormState::failed("Sorry — we couldn't send that just now. Please try again, or email us directly.", $values);
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
        $message = new \App\Mail\PlainMessage($subject, $body);
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
