@php
    $isReset = $purpose === \App\Support\EmailOtp::PURPOSE_RESET;
    $mainSite = rtrim(config('app.main_site_url'), '/');
    $heading = $isReset ? 'Reset your password' : 'Verify your email address';
    $intro = $isReset
        ? 'We received a request to reset the password for your Nepal Notary Public Council account. Enter the code below to continue.'
        : 'Thank you for registering with the Nepal Notary Public Council. Enter the code below to verify your email address and activate your account.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $heading }}</title>
    <style>
        @media only screen and (max-width: 480px) {
            .email-outer { padding: 16px 8px !important; }
            .email-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .email-code { font-size: 28px !important; letter-spacing: 6px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#eef1f6; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#1f2937;">
    {{-- inbox preview text --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Your verification code is {{ $otp }}. It expires in {{ $ttlMinutes }} minutes.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
        <tr>
            <td align="center" class="email-outer" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 18px rgba(0,36,99,0.08);">

                    {{-- crimson / blue accent, matching the site footer --}}
                    <tr>
                        <td style="height:5px; line-height:5px; font-size:0; background-color:#003893; background-image:linear-gradient(90deg, #dc143c 0%, #dc143c 50%, #ffffff 50%, #ffffff 52%, #0050c8 52%, #0050c8 100%);">&nbsp;</td>
                    </tr>

                    {{-- header --}}
                    <tr>
                        <td align="center" style="padding:28px 32px 20px; border-bottom:1px solid #e5e9f0;">
                            <img src="{{ $message->embed(public_path('frontend/images/logo.png')) }}" alt="Nepal Notary Public Council" width="96" style="display:block; width:96px; height:auto; border:0; margin:0 auto 12px;">
                            <div style="font-size:20px; font-weight:700; color:#003893; line-height:1.3;">Nepal Notary Public Council</div>
                            <div style="font-size:15px; font-weight:600; color:#003893; line-height:1.5;">नेपाल नोटरी पब्लिक परिषद्</div>
                            <div style="font-size:12px; color:#dc143c; font-weight:600; letter-spacing:0.5px; margin-top:2px;">Kathmandu, Nepal</div>
                        </td>
                    </tr>

                    {{-- body --}}
                    <tr>
                        <td class="email-pad" style="padding:32px 40px 8px;">
                            <h1 style="margin:0 0 12px; font-size:22px; line-height:1.35; color:#111827; font-weight:700;">{{ $heading }}</h1>
                            <p style="margin:0 0 24px; font-size:15px; line-height:1.65; color:#4b5563;">Namaste,</p>
                            <p style="margin:0 0 24px; font-size:15px; line-height:1.65; color:#4b5563;">{{ $intro }}</p>
                        </td>
                    </tr>

                    {{-- code --}}
                    <tr>
                        <td class="email-pad" align="center" style="padding:0 40px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f6fc; border:1px dashed #9db4e0; border-radius:10px;">
                                <tr>
                                    <td align="center" style="padding:22px 16px;">
                                        <div style="font-size:12px; font-weight:600; letter-spacing:1.5px; text-transform:uppercase; color:#6b7280; margin-bottom:8px;">
                                            {{ $isReset ? 'Password reset code' : 'Verification code' }}
                                        </div>
                                        <div class="email-code" style="font-family:'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size:34px; font-weight:700; letter-spacing:10px; color:#003893; padding-left:10px;">{{ $otp }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-pad" style="padding:20px 40px 8px;">
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#4b5563; text-align:center;">
                                This code expires in <strong style="color:#111827;">{{ $ttlMinutes }} minutes</strong>.
                            </p>

                            {{-- security note --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#fff6f7; border-left:4px solid #dc143c; border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 16px; font-size:13px; line-height:1.6; color:#6b2131;">
                                        <strong>Keep this code private.</strong> Council staff will never ask you for it.
                                        If you did not {{ $isReset ? 'request a password reset' : 'create an account' }}, you can safely ignore this email.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:28px 0 32px; font-size:15px; line-height:1.65; color:#4b5563;">
                                Regards,<br>
                                <strong style="color:#111827;">Nepal Notary Public Council</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- footer --}}
                    <tr>
                        <td style="background-color:#002463; padding:24px 32px; text-align:center;">
                            <p style="margin:0 0 6px; font-size:13px; line-height:1.6; color:#dbe4f5;">
                                Phone: <a href="tel:015452357" style="color:#ffffff; text-decoration:none;">01-5452357</a>
                                &nbsp;&middot;&nbsp;
                                Email: <a href="mailto:nepalnotary@gmail.com" style="color:#ffffff; text-decoration:none;">nepalnotary@gmail.com</a>
                            </p>
                            <p style="margin:0 0 12px; font-size:13px; line-height:1.6;">
                                <a href="{{ $mainSite }}" style="color:#ffffff; font-weight:600; text-decoration:underline;">{{ preg_replace('#^https?://#', '', $mainSite) }}</a>
                            </p>
                            <p style="margin:0; font-size:11px; line-height:1.6; color:#9fb1d6;">
                                &copy; {{ date('Y') }} Nepal Notary Public Council. All rights reserved.<br>
                                This is an automated message, please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
