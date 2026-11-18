<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * "One item per line" admin fields (product "Best for", awards, incubators). Each line becomes a list
 * item on the website, so both the number of lines and each line's length are limited to keep the layout tidy.
 */
class ListField
{
    /** @return list<string> */
    public static function lines(?string $text, string $field, int $maxLines, int $maxLength): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));

        if (count($lines) > $maxLines) {
            throw ValidationException::withMessages([$field => "Please keep this to {$maxLines} lines or fewer."]);
        }
        foreach ($lines as $i => $line) {
            if (mb_strlen($line) > $maxLength) {
                throw ValidationException::withMessages([$field => 'Line '.($i + 1)." is too long — keep each line to {$maxLength} characters."]);
            }
        }

        return $lines;
    }
}
