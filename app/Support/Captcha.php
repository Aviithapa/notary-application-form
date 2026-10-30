<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/**
 * Self-hosted image captcha. The code is kept (hashed) in the session, is
 * single-use and expires after a few minutes.
 */
class Captcha
{
    // no 0/O, 1/I/L to avoid ambiguous characters
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const LENGTH = 5;
    private const TTL_SECONDS = 600;

    public function __construct(private string $key = 'contact')
    {
    }

    /**
     * Create a new code, remember it in the session and return the PNG bytes.
     */
    public function image(int $width = 180, int $height = 56): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        session()->put($this->sessionKey(), [
            'hash' => Hash::make($code),
            'expires_at' => now()->addSeconds(self::TTL_SECONDS)->timestamp,
        ]);

        return $this->render($code, $width, $height);
    }

    /**
     * Check the user's answer. The stored code is discarded either way, so a
     * code can only be tried once.
     */
    public function check(?string $answer): bool
    {
        $stored = session()->pull($this->sessionKey());

        if (!$stored || !$answer || $stored['expires_at'] < now()->timestamp) {
            return false;
        }

        return Hash::check(strtoupper(trim($answer)), $stored['hash']);
    }

    private function render(string $code, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 241, 244, 250));

        // background noise
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($image, random_int(150, 200), random_int(160, 210), random_int(190, 230));
            imagesetthickness($image, random_int(1, 2));
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $color);
        }
        for ($i = 0; $i < 180; $i++) {
            $color = imagecolorallocate($image, random_int(140, 210), random_int(150, 210), random_int(180, 230));
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
        }

        // characters in brand blues/crimson with random tilt
        $palette = [[0, 56, 147], [0, 41, 107], [220, 20, 60], [40, 70, 140]];
        $font = resource_path('fonts/captcha.ttf');
        $step = ($width - 24) / self::LENGTH;
        foreach (str_split($code) as $i => $char) {
            [$r, $g, $b] = $palette[array_rand($palette)];
            $color = imagecolorallocate($image, $r, $g, $b);
            $x = (int) (14 + $i * $step + random_int(-2, 3));
            $y = (int) ($height / 2 + 11 + random_int(-4, 4));

            if (function_exists('imagettftext') && is_file($font)) {
                imagettftext($image, random_int(22, 26), random_int(-22, 22), $x, $y, $color, $font, $char);
            } else {
                imagestring($image, 5, $x, (int) ($height / 2 - 8), $char, $color);
            }
        }

        // a strike-through curve over the text
        $color = imagecolorallocate($image, 0, 56, 147);
        imagesetthickness($image, 2);
        $amplitude = random_int(4, 8);
        $phase = random_int(0, 314) / 100;
        $prevY = null;
        for ($x = 0; $x < $width; $x += 2) {
            $y = (int) ($height / 2 + sin($x / 18 + $phase) * $amplitude);
            if ($prevY !== null) {
                imageline($image, $x - 2, $prevY, $x, $y, $color);
            }
            $prevY = $y;
        }

        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    private function sessionKey(): string
    {
        return 'captcha.' . $this->key;
    }
}
