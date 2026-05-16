<?php

declare(strict_types=1);

class Validator
{
    public static function required($value): bool
    {
        if (is_array($value)) {
            return count($value) > 0;
        }

        return trim((string) $value) !== '';
    }

    public static function numeric($value): bool
    {
        return is_numeric($value);
    }

    public static function integer($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function positive($value): bool
    {
        return is_numeric($value) && (float) $value > 0;
    }

    public static function email($value): bool
    {
        if (!self::required($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function min($value, int $length): bool
    {
        return self::length($value) >= $length;
    }

    public static function max($value, int $length): bool
    {
        return self::length($value) <= $length;
    }

    public static function same($value, $other): bool
    {
        return (string) $value === (string) $other;
    }

    public static function in($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    public static function date($value): bool
    {
        if (!self::required($value)) {
            return true;
        }

        $date = DateTime::createFromFormat('Y-m-d', (string) $value);

        return $date && $date->format('Y-m-d') === $value;
    }

    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $ruleName = $rule;
                $ruleValue = null;

                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                }

                $value = $data[$field] ?? null;

                if (!self::passes($ruleName, $value, $ruleValue, $data)) {
                    $errors[$field] = self::message($field, $ruleName, $ruleValue);
                    break;
                }
            }
        }

        return $errors;
    }

    private static function passes(string $rule, $value, $ruleValue, array $data): bool
    {
        switch ($rule) {
            case 'required':
                return self::required($value);

            case 'numeric':
                return self::numeric($value);

            case 'integer':
                return self::integer($value);

            case 'positive':
                return self::positive($value);

            case 'email':
                return self::email($value);

            case 'min':
                return self::min($value, (int) $ruleValue);

            case 'max':
                return self::max($value, (int) $ruleValue);

            case 'date':
                return self::date($value);

            case 'same':
                return self::same($value, $data[$ruleValue] ?? null);

            default:
                return true;
        }
    }

    private static function message(string $field, string $rule, $ruleValue = null): string
    {
        $label = str_replace('_', ' ', $field);

        switch ($rule) {
            case 'required':
                return ucfirst($label) . ' wajib diisi.';

            case 'numeric':
                return ucfirst($label) . ' harus berupa angka.';

            case 'integer':
                return ucfirst($label) . ' harus berupa angka bulat.';

            case 'positive':
                return ucfirst($label) . ' harus lebih dari 0.';

            case 'email':
                return ucfirst($label) . ' harus berupa email valid.';

            case 'min':
                return ucfirst($label) . ' minimal ' . $ruleValue . ' karakter.';

            case 'max':
                return ucfirst($label) . ' maksimal ' . $ruleValue . ' karakter.';

            case 'date':
                return ucfirst($label) . ' harus format tanggal YYYY-MM-DD.';

            case 'same':
                return ucfirst($label) . ' tidak sama.';

            default:
                return ucfirst($label) . ' tidak valid.';
        }
    }

    private static function length($value): int
    {
        if (is_array($value)) {
            return count($value);
        }

        $value = trim((string) $value);

        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}