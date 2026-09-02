<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function required(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function string(
        string $field,
        mixed $value,
        int $maxLength,
        string $message
    ): self {
        if (! is_string($value)) {
            $this->errors[$field][] = $message;

            return $this;
        }

        if (mb_strlen($value) > $maxLength) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function integer(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            filter_var(
                $value,
                FILTER_VALIDATE_INT
            ) === false
        ) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function positiveNumber(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            ! is_numeric($value)
            || (float) $value <= 0
        ) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function date(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (! is_string($value)) {
            $this->errors[$field][] = $message;

            return $this;
        }

        $date = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $value
        );

        $valid = $date !== false
            && $date->format('Y-m-d') === $value;

        if (! $valid) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    public function addError(
        string $field,
        string $message
    ): self {
        $this->errors[$field][] = $message;

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
