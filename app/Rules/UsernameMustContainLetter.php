<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UsernameMustContainLetter implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! preg_match('/\p{L}/u', $value)) {
            $fail('The username field requires letters and may include numbers; it cannot be numbers only.');
        }
    }
}
