<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; background: #fef9ef; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef9ef; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    {{-- Logo Bar --}}
                    <tr>
                        <td style="text-align: center; padding-bottom: 24px;">
                            <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 28px; font-weight: 700; color: #1a1040; letter-spacing: -0.5px;">Mouse</span><span style="font-family: 'Playfair Display', Georgia, serif; font-size: 28px; font-weight: 700; color: #d4a843; letter-spacing: -0.5px;">28</span>
                        </td>
                    </tr>

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 60%, #3d2580 100%); border-radius: 16px 16px 0 0; padding: 32px 40px; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.12em; color: #d4a843; font-weight: 600;">New Message</p>
                            <h1 style="margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.3;">{{ $contactMessage->subject_label }}</h1>
                        </td>
                    </tr>

                    {{-- Gold accent line --}}
                    <tr>
                        <td style="background: linear-gradient(90deg, #d4a843, #f0c75e, #d4a843); height: 3px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background: #ffffff; padding: 36px 40px;">

                            {{-- Sender Info --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 28px; background: #f8f4ec; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td>
                                                    <p style="margin: 0 0 2px; font-size: 16px; font-weight: 700; color: #1a1040;">{{ $contactMessage->name }}</p>
                                                    <a href="mailto:{{ $contactMessage->email }}" style="font-size: 13px; color: #5b3e9e; text-decoration: none; font-weight: 500;">{{ $contactMessage->email }}</a>
                                                </td>
                                                <td align="right" valign="top">
                                                    <p style="margin: 0; font-size: 12px; color: #9a8a6a; font-weight: 500;">{{ $contactMessage->created_at->format('M j, Y') }}</p>
                                                    <p style="margin: 2px 0 0; font-size: 12px; color: #b8a88a;">{{ $contactMessage->created_at->format('g:i A') }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Message --}}
                            <div style="border-left: 3px solid #d4a843; padding: 20px 24px; background: #fef9ef; border-radius: 0 12px 12px 0; margin-bottom: 32px;">
                                <p style="margin: 0; font-size: 15px; line-height: 1.75; color: #2a2040; white-space: pre-wrap;">{{ $contactMessage->message }}</p>
                            </div>

                            {{-- Reply Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($contactMessage->subject_label) }}"
                                           style="display: inline-block; background: linear-gradient(135deg, #2d1b69, #1a1040); color: #ffffff; font-size: 14px; font-weight: 600; padding: 14px 36px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em;">
                                            ✉️&nbsp;&nbsp;Reply to {{ $contactMessage->name }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background: #1a1040; border-radius: 0 0 16px 16px; padding: 20px 40px; text-align: center;">
                            <p style="margin: 0 0 6px; font-size: 12px; color: rgba(255,255,255,0.4);">
                                <a href="{{ url('/admin') }}" style="color: #d4a843; text-decoration: none; font-weight: 600;">View in Admin</a>
                                &nbsp;&middot;&nbsp;
                                <a href="{{ url('/') }}" style="color: rgba(255,255,255,0.5); text-decoration: none;">mouse28.com</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
