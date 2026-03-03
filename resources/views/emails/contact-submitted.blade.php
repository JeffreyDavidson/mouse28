<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background: #fef9ef; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef9ef; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="570" cellpadding="0" cellspacing="0" style="max-width: 570px; width: 100%;">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a1040, #2d1b69); border-radius: 12px 12px 0 0; padding: 28px 32px; text-align: center;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #fef9ef;">New Contact Message</h1>
                            <p style="margin: 6px 0 0; font-size: 13px; color: rgba(254,249,239,0.5);">mouse28.com</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background: #ffffff; padding: 32px; border: 1px solid rgba(26,16,64,0.08); border-top: none;">
                            {{-- Meta --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #f0ebe0;">
                                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #9a8a6a; font-weight: 600;">From</span><br>
                                        <span style="font-size: 15px; color: #1a1040; font-weight: 600;">{{ $contactMessage->name }}</span>
                                        <span style="font-size: 13px; color: #7a6a5a;"> &lt;{{ $contactMessage->email }}&gt;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #f0ebe0;">
                                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #9a8a6a; font-weight: 600;">Topic</span><br>
                                        <span style="font-size: 14px; color: #5b3e9e; font-weight: 600;">{{ $contactMessage->subject_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #9a8a6a; font-weight: 600;">Received</span><br>
                                        <span style="font-size: 13px; color: #7a6a5a;">{{ $contactMessage->created_at->format('M j, Y \a\t g:i A') }}</span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Message --}}
                            <div style="background: #fef9ef; border-radius: 8px; padding: 20px; border-left: 3px solid #d4a843;">
                                <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #3a2a1a; white-space: pre-wrap;">{{ $contactMessage->message }}</p>
                            </div>

                            {{-- Reply button --}}
                            <div style="text-align: center; margin-top: 28px;">
                                <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($contactMessage->subject_label) }}" style="display: inline-block; background: linear-gradient(135deg, #d4a843, #b8922e); color: #1a1040; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 8px; text-decoration: none;">Reply to {{ $contactMessage->name }}</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background: #f5efe0; border-radius: 0 0 12px 12px; padding: 16px 32px; text-align: center; border: 1px solid rgba(26,16,64,0.05); border-top: none;">
                            <p style="margin: 0; font-size: 12px; color: #9a8a6a;">View in <a href="{{ url('/admin') }}" style="color: #5b3e9e; text-decoration: none; font-weight: 600;">Admin Panel</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
