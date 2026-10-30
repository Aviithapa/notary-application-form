<?php

namespace App\Support\NotaryDirectory;

use Illuminate\Support\Collection;

/**
 * Placeholder records for the Notary Namespace lists until real data is available.
 *
 * Every text value is stored as ['en' => ..., 'np' => ...] so the pages can show the
 * active locale. Replace each method with a database query returning the same shape.
 */
class DummyDirectory
{
    private const FIRST_NAMES = [
        ['Ram', 'राम'], ['Sita', 'सीता'], ['Hari', 'हरि'], ['Gita', 'गीता'], ['Krishna', 'कृष्ण'],
        ['Sarita', 'सरिता'], ['Bishnu', 'विष्णु'], ['Anita', 'अनिता'], ['Rajendra', 'राजेन्द्र'],
        ['Sunita', 'सुनिता'], ['Mohan', 'मोहन'], ['Kamala', 'कमला'], ['Prakash', 'प्रकाश'],
        ['Laxmi', 'लक्ष्मी'], ['Dipak', 'दीपक'], ['Bimala', 'बिमला'],
    ];

    private const SURNAMES = [
        ['Thapa', 'थापा'], ['Shrestha', 'श्रेष्ठ'], ['Sharma', 'शर्मा'], ['Adhikari', 'अधिकारी'],
        ['Gurung', 'गुरुङ'], ['Karki', 'कार्की'], ['Poudel', 'पौडेल'], ['Rai', 'राई'],
        ['Bhandari', 'भण्डारी'], ['Maharjan', 'महर्जन'], ['Khadka', 'खड्का'], ['Joshi', 'जोशी'],
    ];

    public const DISTRICTS = [
        ['Kathmandu', 'काठमाडौं'], ['Lalitpur', 'ललितपुर'], ['Bhaktapur', 'भक्तपुर'], ['Kaski', 'कास्की'],
        ['Chitwan', 'चितवन'], ['Morang', 'मोरङ'], ['Jhapa', 'झापा'], ['Rupandehi', 'रुपन्देही'],
        ['Banke', 'बाँके'], ['Kailali', 'कैलाली'], ['Parsa', 'पर्सा'], ['Sunsari', 'सुनसरी'],
        ['Dang', 'दाङ'], ['Makwanpur', 'मकवानपुर'], ['Syangja', 'स्याङ्जा'],
    ];

    public const WORK_TYPES = [
        ['Notarization', 'नोटरी प्रमाणीकरण'],
        ['Translation', 'अनुवाद'],
        ['Notarization & Translation', 'नोटरी प्रमाणीकरण तथा अनुवाद'],
    ];

    private const REMARKS = [
        ['', ''], ['', ''], ['', ''], ['Renewed', 'नवीकरण गरिएको'], ['Temporary license', 'अस्थायी इजाजत'],
    ];

    /** Notary Public List */
    public static function notaries(): Collection
    {
        return self::people(72, 101)->map(fn($p, $i) => [
            'number' => (string) (1001 + $i),
            'name' => $p['name'],
            'photo' => null,
            'permanent_address' => $p['home'],
            'working_district' => $p['district'],
            'nature_of_work' => $p['work'],
            'contact' => $p['phone'],
            'remark' => self::pick(self::REMARKS, $i, 7),
        ]);
    }

    /** Translators List */
    public static function translators(): Collection
    {
        return self::people(48, 202)->map(fn($p, $i) => [
            'pp_number' => (string) (501 + $i),
            'name' => $p['name'],
            'homeland' => $p['home'],
            'working_district' => $p['district'],
            'contact' => $p['phone'],
            'nature_of_work' => self::pick([self::WORK_TYPES[1], self::WORK_TYPES[2]], $i, 3),
        ]);
    }

    /** Cancelled Translators List */
    public static function cancelledTranslators(): Collection
    {
        return self::people(23, 303)->map(fn($p, $i) => [
            'number' => (string) (601 + $i * 3),
            'name' => $p['name'],
        ]);
    }

    /** District Change (Notary Public List) */
    public static function districtChanges(): Collection
    {
        return self::people(36, 404)->map(function ($p, $i) {
            $from = $p['district'];
            $to = self::pick(self::DISTRICTS, $i, 11);
            if ($to === $from) {
                $to = self::pick(self::DISTRICTS, $i + 1, 11);
            }

            // Bikram Sambat dates, newest first
            $month = 12 - ($i % 12);
            $day = 1 + (($i * 7) % 28);
            $year = 2081 - intdiv($i, 12);
            $date = sprintf('%d-%02d-%02d', $year, $month, $day);

            return [
                'sn' => (string) ($i + 1),
                'pp_number' => (string) (1001 + (($i * 17) % 72)),
                'name' => $p['name'],
                'previous_district' => $from,
                'changed_district' => $to,
                'decision_date' => $date,
            ];
        });
    }

    /**
     * Deterministic fake people so the lists stay the same between requests.
     */
    private static function people(int $count, int $seed): Collection
    {
        return collect(range(0, $count - 1))->map(function ($i) use ($seed) {
            $first = self::pick(self::FIRST_NAMES, $i + $seed, 5);
            $last = self::pick(self::SURNAMES, $i + $seed, 7);

            return [
                'name' => ['en' => $first['en'] . ' ' . $last['en'], 'np' => $first['np'] . ' ' . $last['np']],
                'home' => self::pick(self::DISTRICTS, $i, 4),
                'district' => self::pick(self::DISTRICTS, $i + $seed, 7),
                'work' => self::pick(self::WORK_TYPES, $i, 1),
                'phone' => '98' . str_pad((string) (($i * 7919 + $seed * 104729) % 100000000), 8, '0', STR_PAD_LEFT),
            ];
        });
    }

    /**
     * Pick a bilingual value from a list of [en, np] pairs. A step that shares no factor
     * with the list size walks through every entry before repeating.
     */
    private static function pick(array $pairs, int $index, int $step): array
    {
        [$en, $np] = $pairs[($index * $step) % count($pairs)];

        return ['en' => $en, 'np' => $np];
    }
}
