<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $issue->title }}</title>
    <style>
        .newsletter-body a { color: #5b3e9e; text-decoration: underline; }
        .newsletter-body img { max-width: 100%; height: auto; }
        .newsletter-body h2, .newsletter-body h3 { font-family: 'Besley', Georgia, serif; color: #1a1040; line-height: 1.3; }
        .newsletter-body blockquote { margin: 0 0 16px; padding: 4px 16px; border-left: 3px solid #d4a843; background: #fef9ef; }
        .newsletter-body pre { overflow-x: auto; padding: 12px; background: #f5efe0; border-radius: 6px; font-size: 14px; }
    </style>
</head>
<body style="margin: 0; padding: 0; background: #fef9ef; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #fef9ef; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background: #1a1040; border-radius: 16px 16px 0 0; padding: 32px 40px;">
                            <p style="margin: 0 0 12px; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: #d4a843;">Mouse28 Newsletter</p>
                            <h1 style="margin: 0; font-family: 'Besley', Georgia, serif; font-size: 26px; font-weight: 700; color: #ffffff; line-height: 1.3;">{{ $issue->title }}</h1>
                        </td>
                    </tr>

                    {{-- Gold accent line --}}
                    <tr>
                        <td style="background: #d4a843; height: 3px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background: #ffffff; padding: 36px 40px;">
                            @if ($unsubscribeUrl === null)
                                <p style="margin: 0 0 24px; padding: 12px 16px; background: #fef3c7; border-radius: 8px; font-size: 14px; color: #5f4310;">
                                    This is a test email. Readers receive their own unsubscribe link in the footer.
                                </p>
                            @endif
                            <div class="newsletter-body" style="font-size: 16px; line-height: 1.7; color: #2a2040;">
                                {!! $bodyHtml !!}
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background: #1a1040; border-radius: 0 0 16px 16px; padding: 24px 40px; font-size: 13px; line-height: 1.7; color: #d9d3ea;">
                            <p style="margin: 0 0 8px;">
                                <a href="{{ $issueUrl }}" style="color: #f0c75e;">Read this issue on the web</a>
                            </p>
                            <p style="margin: 0;">
                                You're receiving this because you confirmed a sign-up for the Mouse28 newsletter.
                                @if ($unsubscribeUrl !== null)
                                    <a href="{{ $unsubscribeUrl }}" style="color: #f0c75e;">Unsubscribe</a>
                                @endif
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
