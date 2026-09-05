<?php

declare(strict_types=1);

namespace App\Core;

abstract class FormRequest
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data !== [] ? $data : $_POST;
    }

    public static function createFromGlobals(): static
    {
        return new static($_POST);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }

    abstract public function rules(): array;

    public function messages(): array
    {
        return [];
    }

    public function validate(): Validator
    {
        $validator = new Validator;
        $rules = $this->rules();
        $messages = $this->messages();

        foreach ($rules as $field => $fieldRules) {
            $value = $this->input($field);

            foreach ((array) $fieldRules as $rule) {
                $customMsgKey = "{$field}.{$rule}";
                $defaultMsg = "Validation failed for {$field}.";
                $message = $messages[$customMsgKey] ?? $defaultMsg;

                switch ($rule) {
                    case 'required':
                        $validator->required($field, $value, $message);
                        break;
                    case 'email':
                        $validator->email($field, $value, $message);
                        break;
                    case 'passwordStrength':
                        $validator->passwordStrength($field, $value, $message);
                        break;
                }
            }
        }

        return $validator;
    }
}

