<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUsername implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // echo $value; die;
        // $pattern = '/^(?=.*[a-zA-Z])(?=.*@).+$/';
        $pattern = '/(?=.*[a-zA-Z])|(?=.*@)/';
        if (is_string($value) && preg_match($pattern, $value)){
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $fail('The :attribute must be valid email id.');
            }
        } else {
            // Define a pattern for UK phone numbers (simple example)
            $ukPhonePattern = '/^(\+44\s?7\d{3}|\(?07\d{3}\)?\s?)\d{3}\s?\d{3}$/';

            // Check if the value matches the UK phone number pattern
            if(!preg_match($ukPhonePattern, $value)){
                $fail('The :attribute must be valid UK phone.');
            }
        }

    }
}
