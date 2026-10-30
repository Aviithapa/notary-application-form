<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New contact inquiry</title>
</head>

<body style="margin:0; padding:0; background-color:#f1f4fa; font-family:Arial, Helvetica, sans-serif; color:#212529;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f4fa; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e3e8f2;">
                    <tr>
                        <td style="height:5px; background-color:#dc143c; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px; background-color:#003893; color:#ffffff;">
                            <p style="margin:0; font-size:12px; letter-spacing:1px; text-transform:uppercase; opacity:0.75;">{{ $siteTitle }}</p>
                            <h1 style="margin:6px 0 0; font-size:20px; font-weight:bold;">New contact inquiry</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0; width:110px; color:#6c757d;">Name</td>
                                    <td style="padding:8px 0; font-weight:bold;">{{ $contact->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#6c757d;">Email</td>
                                    <td style="padding:8px 0;"><a href="mailto:{{ $contact->email }}" style="color:#003893;">{{ $contact->email }}</a></td>
                                </tr>
                                @if ($contact->phone)
                                    <tr>
                                        <td style="padding:8px 0; color:#6c757d;">Phone</td>
                                        <td style="padding:8px 0;"><a href="tel:{{ $contact->phone }}" style="color:#003893;">{{ $contact->phone }}</a></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:8px 0; color:#6c757d;">Received</td>
                                    <td style="padding:8px 0;">{{ $contact->created_at?->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 8px; font-size:12px; letter-spacing:1px; text-transform:uppercase; color:#6c757d;">Message</p>
                            <div style="padding:16px 18px; border-left:3px solid #dc143c; background-color:#f8f9fc; border-radius:6px; font-size:14px; line-height:1.7; white-space:pre-line;">{{ $contact->message }}</div>

                            <p style="margin:24px 0 0;">
                                <a href="mailto:{{ $contact->email }}?subject={{ rawurlencode('Re: Your inquiry to ' . $siteTitle) }}"
                                    style="display:inline-block; padding:11px 20px; border-radius:8px; background-color:#003893; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px;">
                                    Reply to {{ $contact->name }}
                                </a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px; border-top:1px solid #eef1f6; font-size:12px; color:#98a2b3;">
                            Sent from the contact form on {{ url('/') }}. Replying to this email goes directly to the sender.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
