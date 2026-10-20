<?php

namespace App\Support;

/**
 * What a form shows after a submit (ported from lib/forms.ts): a status, a message, per-field errors, the values to
 * keep in the fields, and, when the form can't be delivered online, a mailto: link to offer instead.
 */
class FormState
{
    public function __construct(
        public string $status = 'idle',
        public string $message = '',
        public array $errors = [],
        public array $values = [],
        public ?string $mailto = null,
    ) {}

    public static function idle(): self
    {
        return new self;
    }

    public static function success(string $message): self
    {
        return new self('success', $message);
    }

    /** "There is 1 problem with your submission." / "There are 3 problems ..." */
    public static function invalid(array $errors, array $values, ?string $message = null): self
    {
        $n = count($errors);

        return new self('error', $message ?? 'There '.($n === 1 ? 'is 1 problem' : "are {$n} problems").' with your submission.', $errors, $values);
    }

    public static function failed(string $message, array $values = [], ?string $mailto = null): self
    {
        return new self('error', $message, [], $values, $mailto);
    }

    public function value(string $key, ?string $default = null): ?string
    {
        return $this->values[$key] ?? $default;
    }

    public function error(string $key): ?string
    {
        return $this->errors[$key] ?? null;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $a): self
    {
        return new self($a['status'] ?? 'idle', $a['message'] ?? '', $a['errors'] ?? [], $a['values'] ?? [], $a['mailto'] ?? null);
    }
}
