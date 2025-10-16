<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class FileOrDeleted implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === '__DELETED__') {
            return;
        }

        if ($value instanceof UploadedFile && $value->isValid()) {
            if (!in_array($value->getMimeType(), ['image/jpeg', 'image/png'])) {
                $fail("The $attribute must be a JPEG or PNG image.");
            }

            if ($value->getSize() > 5 * 1024 * 1024) { // 5MB
                $fail("The $attribute must not be larger than 5MB.");
            }

            return;
        }

        $fail("The $attribute must be a valid file or the string 'DELETED'.");
    }
}
