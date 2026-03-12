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
                        <td style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 60%, #3d2580 100%); border-radius: 16px 16px 0 0; padding: 36px 40px; text-align: center;">
                            <p style="margin: 0 0 12px; font-size: 36px; line-height: 1;">✨</p>
                            <h1 style="margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.3;">Thanks for reaching out!</h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: rgba(255,255,255,0.6);">We received your message and will get back to you soon.</p>
                        </td>
                    </tr>

                    {{-- Gold accent line --}}
                    <tr>
                        <td style="background: linear-gradient(90deg, #d4a843, #f0c75e, #d4a843); height: 3px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background: #ffffff; padding: 36px 40px;">

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #2a2040;">
                                Hi {{ $contactMessage->name }},
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #2a2040;">
                                Thanks for getting in touch with us at Mouse28! We've received your message about <strong style="color: #1a1040;">{{ $contactMessage->subject_label }}</strong> and will do our best to respond within 48 hours.
                            </p>

                            {{-- Their message recap --}}
                            <p style="margin: 0 0 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.12em; color: #9a8a6a; font-weight: 600;">Your message</p>
                            <div style="border-left: 3px solid #d4a843; padding: 16px 20px; background: #fef9ef; border-radius: 0 12px 12px 0; margin-bottom: 28px;">
                                <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #3a2a1a; white-space: pre-wrap;">{{ $contactMessage->message }}</p>
                            </div>

                            <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.7; color: #2a2040;">
                                In the meantime, check out our latest episodes and blog posts!
                            </p>

                            {{-- CTA Buttons --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/episodes') }}"
                                           style="display: inline-block; background: linear-gradient(135deg, #2d1b69, #1a1040); color: #ffffff; font-size: 14px; font-weight: 600; padding: 14px 32px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em; margin: 0 6px;">
                                            🎙️&nbsp;&nbsp;Listen Now
                                        </a>
                                        <a href="{{ url('/blog') }}"
                                           style="display: inline-block; background: transparent; color: #2d1b69; font-size: 14px; font-weight: 600; padding: 12px 32px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em; border: 2px solid #2d1b69; margin: 0 6px;">
                                            📖&nbsp;&nbsp;Read Blog
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background: #1a1040; border-radius: 0 0 16px 16px; padding: 24px 40px; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: rgba(255,255,255,0.5);">
                                Jeffrey &amp; Cassie Davidson
                            </p>
                            <p style="margin: 0; font-size: 12px; color: rgba(255,255,255,0.35);">
                                <a href="{{ url('/') }}" style="color: #d4a843; text-decoration: none;">mouse28.com</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
