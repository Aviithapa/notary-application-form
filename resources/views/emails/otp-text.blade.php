Nepal Notary Public Council
नेपाल नोटरी पब्लिक परिषद्

@if ($purpose === \App\Support\EmailOtp::PURPOSE_RESET)
Reset your password

We received a request to reset the password for your Nepal Notary Public Council account. Enter the code below to continue.
@else
Verify your email address

Thank you for registering with the Nepal Notary Public Council. Enter the code below to verify your email address and activate your account.
@endif

Your code: {{ $otp }}

This code expires in {{ $ttlMinutes }} minutes. Keep it private, council staff will never ask you for it. If you did not request it, you can safely ignore this email.

Regards,
Nepal Notary Public Council
Phone: 01-5452357 | Email: nepalnotary@gmail.com
{{ config('app.main_site_url') }}
