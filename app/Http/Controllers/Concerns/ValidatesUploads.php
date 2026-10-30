<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ValidatesUploads
{
    /**
     * Reject anything that is not an image or office/PDF document before it reaches storage.
     */
    protected function validateUploads(Request $request, array $extraRules = []): void
    {
        $request->validate(array_merge([
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv|max:10240',
        ], $extraRules), [
            'file.mimes' => 'Only images (jpg, png, gif, webp) or PDF files are allowed.',
            'files.*.mimes' => 'Only images, PDF, Word, Excel or CSV files are allowed.',
        ]);
    }
}
