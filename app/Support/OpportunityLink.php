<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;

class OpportunityLink
{
    public static function isValid(string $value): bool
    {
        $value = trim($value);

        if (preg_match('/[\x00-\x20\x7f]/', $value)) {
            return false;
        }

        if (str_starts_with(strtolower($value), 'mailto:')) {
            $address = explode('?', substr($value, 7), 2)[0];

            return filter_var(rawurldecode($address), FILTER_VALIDATE_EMAIL) !== false;
        }

        return Validator::make(['url' => $value], ['url' => 'required|url:http,https'])->passes();
    }
}
