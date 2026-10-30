<?php

namespace App\Support;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * One-time codes sent by email for registration and password reset.
 * Only a hash of the code is kept, and it expires or locks after too many wrong guesses.
 */
class EmailOtp
{
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_RESET = 'reset';

    public const TTL_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;

    public function send(string $email, string $purpose): void
    {
        $otp = (string) random_int(100000, 999999);

        Cache::put($this->key($email, $purpose), [
            'hash' => Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));
        Cache::put($this->cooldownKey($email, $purpose), true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        Mail::to($email)->queue(new OtpMail($otp, $purpose, self::TTL_MINUTES));
    }

    public function canResend(string $email, string $purpose): bool
    {
        return ! Cache::has($this->cooldownKey($email, $purpose));
    }

    public function verify(string $email, string $purpose, string $otp): bool
    {
        $key = $this->key($email, $purpose);
        $entry = Cache::get($key);

        if (! $entry) {
            return false;
        }

        if (Hash::check(trim($otp), $entry['hash'])) {
            Cache::forget($key);

            return true;
        }

        $entry['attempts']++;
        if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($key);
        } else {
            Cache::put($key, $entry, now()->addMinutes(self::TTL_MINUTES));
        }

        return false;
    }

    private function key(string $email, string $purpose): string
    {
        return 'email-otp:' . $purpose . ':' . sha1(Str::lower($email));
    }

    private function cooldownKey(string $email, string $purpose): string
    {
        return $this->key($email, $purpose) . ':cooldown';
    }
}
