<?php

namespace App\Models\Concerns;

trait HasLocalizedFields
{
    /**
     * Value of a translatable field (e.g. title, content, excerpt) for the active locale.
     * Uses the `<field>_np` column when the site is in Nepali and it has a value,
     * otherwise falls back to the English column.
     */
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'np' && filled($this->{$field . '_np'})) {
            return $this->{$field . '_np'};
        }

        return $this->{$field};
    }
}
