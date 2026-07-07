<?php

namespace App\Core;

class Validator
{
    private array $rules = [];
    private array $data = [];
    private array $error = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function validate(array $data)
    {
        $this->data = $data;
        $this->error = [];

        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return $this->error;
    }

    private function applyRule($field, $rule)
    {
        $value = $this->data[$field] ?? null;

        if (str_contains($rule, ':')) {
            [$ruleName, $param] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        match ($ruleName) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            default => null
        };
    }

    private function validateRequired($field, $value)
    {
        if (empty($value)) {
            $this->error[$field] = ucfirst($field) . " is required";
        }
    }

    private function validateEmail($field, $value){
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)){
            $this->error[$field] = ucfirst($field) . " must be a valid email";
        }
    }
}
