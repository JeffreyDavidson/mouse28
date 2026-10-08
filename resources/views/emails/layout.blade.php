{{--
    The shared document for the HTML emails, used with @component('emails.layout').
    Mail clients ignore most stylesheets, so every style stays inline. The header and
    footer slots carry their cell's style as a slot attribute; the default slot fills
    the white body cell. Optional data: title, styles (slot), logo, accent.
    It lives in emails/ rather than components/ because Pint's Blade formatter skips
    mail views, and its rewrite would change the HTML the mail clients receive.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @isset($title)
        <title>{{ $title }}</title>
    @endisset
    {{ $styles ?? '' }}
</head>
<body style="margin: 0; padding: 0; background: #fef9ef; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #fef9ef; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    @if ($logo ?? false)
                        {{-- Logo Bar --}}
                        <tr>
                            <td style="text-align: center; padding-bottom: 24px;">
                                <span style="font-family: 'Besley', Georgia, serif; font-size: 28px; font-weight: 700; color: #1a1040; letter-spacing: -0.5px;">Mouse</span><span style="font-family: 'Besley', Georgia, serif; font-size: 28px; font-weight: 700; color: #d4a843; letter-spacing: -0.5px;">28</span>
                            </td>
                        </tr>
                    @endif

                    {{-- Header --}}
                    <tr>
                        <td {{ $header->attributes }}>
                            {{ $header }}
                        </td>
                    </tr>

                    {{-- Gold accent line --}}
                    <tr>
                        <td style="background: {{ $accent ?? 'linear-gradient(90deg, #d4a843, #f0c75e, #d4a843)' }}; height: 3px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background: #ffffff; padding: 36px 40px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td {{ $footer->attributes }}>
                            {{ $footer }}
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
