<?php

namespace App\Support;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Throwable;

/**
 * AD <-> BS conversion for Y-m-d strings. Strict: an impossible day (e.g. 2047-01-32) is
 * rejected instead of rolling over into the next month.
 */
class NepaliDate
{
    private const PATTERN = '/^\d{4}-\d{2}-\d{2}$/';

    /** Turns Nepali digits (२०४७-०२-०१) into ASCII and trims the value. */
    public static function normalize(?string $date): ?string
    {
        if ($date === null || trim($date) === '') {
            return null;
        }

        return strtr(trim($date), array_combine(
            ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        ));
    }

    public static function adToBs(?string $ad): ?string
    {
        $ad = self::normalize($ad);
        if ($ad === null || ! preg_match(self::PATTERN, $ad)) {
            return null;
        }

        try {
            return LaravelNepaliDate::from($ad, strict: true)->toNepaliDate();
        } catch (Throwable) {
            return null;
        }
    }

    public static function bsToAd(?string $bs): ?string
    {
        $bs = self::normalize($bs);
        if ($bs === null || ! preg_match(self::PATTERN, $bs)) {
            return null;
        }

        try {
            return LaravelNepaliDate::from($bs, strict: true)->toEnglishDate();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Fills in whichever side is missing. Returns [ad, bs, errorField]: errorField names the
     * input that could not be converted ('ad' or 'bs'), or that disagrees with the other ('bs').
     */
    public static function reconcile(?string $ad, ?string $bs): array
    {
        $ad = self::normalize($ad);
        $bs = self::normalize($bs);

        if ($ad === null && $bs === null) {
            return [null, null, null];
        }

        if ($ad !== null) {
            $converted = self::adToBs($ad);
            if ($converted === null) {
                return [$ad, $bs, 'ad'];
            }
            if ($bs !== null && $bs !== $converted) {
                return [$ad, $bs, 'bs'];
            }

            return [$ad, $converted, null];
        }

        $converted = self::bsToAd($bs);

        return $converted === null ? [$ad, $bs, 'bs'] : [$converted, $bs, null];
    }
}
