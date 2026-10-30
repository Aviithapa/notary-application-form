<?php

 
use Illuminate\Support\Carbon;
 

function generateRandomUsername($length = 8)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $username = '';

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $characters[rand(0, strlen($characters) - 1)];
        $username .= $randomChar;
    }

    return $username;
}
   

if (!function_exists('carbon')) {
    /**
     * @param null $type
     * @return string
     */
    function carbon(Carbon $date, $format = 'Y-m-d')
    {
        return $date->setTimezone('Asia/Kathmandu')->format($format);
    }
}



if (!function_exists('generateSlug')) {
    /**
     * Generates the alias equivalent for the provided string
     *
     * @param $string
     * @return mixed|string
     */
    function generateSlug($string)
    {
        $string = strtolower($string);
        $string = str_replace(" ", "-", $string);
        $string = str_replace(".", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("&", "and", $string);
        $string = str_replace("@", "", $string);
        $string = str_replace("(", "", $string);
        $string = str_replace(")", "", $string);
        $string = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $string);
        return $string;
    }
}

if (!function_exists('switchLanguage')) {

    function switchLanguage($lang)
    {
        if (array_key_exists($lang, config('app.available_locales'))) {
            session()->put('locale', $lang);
        }

        // only go back to pages on this site, never to a host taken from the Referer header
        $previous = url()->previous();
        $sameHost = parse_url($previous, PHP_URL_HOST) === request()->getHost();

        return redirect()->to($sameHost ? $previous : url('/'));
    }
}


 

if (!function_exists('truncateText')) {
    /**
     * Truncate the given title to 20 words and append ellipsis if necessary.
     *
     * @param string $title
     * @param int $wordCount
     * @return string
     */
    function truncateText($title,  $charCount = 100)
    {
        if (strlen($title) > $charCount) {
            return substr($title, 0, $charCount) . '...';
        }
        return $title;
    }
}


 
if (!function_exists('toNepaliDigits')) {
    /**
     * Convert ASCII digits to Devanagari digits, e.g. 2081 -> २०८१.
     */
    function toNepaliDigits(string $value): string
    {
        return strtr($value, ['0' => '०', '1' => '१', '2' => '२', '3' => '३', '4' => '४', '5' => '५', '6' => '६', '7' => '७', '8' => '८', '9' => '९']);
    }
}

if (!function_exists('toLocaleDigits')) {
    /**
     * Digits in the active locale: Devanagari for Nepali, unchanged otherwise.
     */
    function toLocaleDigits($value): string
    {
        return app()->getLocale() === 'np' ? toNepaliDigits((string) $value) : (string) $value;
    }
}

 
