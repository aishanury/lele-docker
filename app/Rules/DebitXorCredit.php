<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class DebitXorCredit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $debit = request('debit');
        $credit = request('credit');

        if ($debit == 0 && $credit == 0) {
            $fail('Either debit or credit must be filled in.');
        } else if ($debit > 0 && $credit > 0) {
            $fail('additionalError', 'Only one of debit or credit can be filled in.');
        }
    }
}
