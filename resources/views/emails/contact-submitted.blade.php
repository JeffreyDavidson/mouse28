@component('emails.layout', ['logo' => true])
    @slot('header', null, ['style' => 'background: linear-gradient(135deg, #1a1040 0%, #2d1b69 60%, #3d2580 100%); border-radius: 16px 16px 0 0; padding: 32px 40px; text-align: center;'])
        <p style="margin: 0 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.12em; color: #d4a843; font-weight: 600;">New Message</p>
        <h1 style="margin: 0; font-family: 'Besley', Georgia, serif; font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.3;">{{ $inquiry->type->getLabel() }}</h1>
    @endslot

    {{-- Sender Info --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 28px; background: #f8f4ec; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <p style="margin: 0 0 2px; font-size: 16px; font-weight: 700; color: #1a1040;">{{ $inquiry->name }}</p>
                            <a href="mailto:{{ $inquiry->email }}" style="font-size: 13px; color: #5b3e9e; text-decoration: none; font-weight: 500;">{{ $inquiry->email }}</a>
                        </td>
                        <td align="right" valign="top">
                            <p style="margin: 0; font-size: 12px; color: #705f41; font-weight: 500;">{{ $receivedAt?->format('M j, Y') }}</p>
                            <p style="margin: 2px 0 0; font-size: 12px; color: #705f41;">{{ $receivedAt?->format('g:i A') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Message --}}
    <div style="border-left: 3px solid #d4a843; padding: 20px 24px; background: #fef9ef; border-radius: 0 12px 12px 0; margin-bottom: 32px;">
        <p style="margin: 0; font-size: 15px; line-height: 1.75; color: #2a2040; white-space: pre-wrap;">{{ $inquiry->message }}</p>
    </div>

    {{-- Reply Button --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $inquiry->replyMailtoUrl() }}"
                   style="display: inline-block; background: linear-gradient(135deg, #2d1b69, #1a1040); color: #ffffff; font-size: 14px; font-weight: 600; padding: 14px 36px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em;">
                    ✉️&nbsp;&nbsp;Reply to {{ $inquiry->name }}
                </a>
            </td>
        </tr>
    </table>

    @slot('footer', null, ['style' => 'background: #1a1040; border-radius: 0 0 16px 16px; padding: 20px 40px; text-align: center;'])
        <p style="margin: 0 0 6px; font-size: 12px; color: rgba(255,255,255,0.4);">
            <a href="{{ $adminUrl }}" style="color: #d4a843; text-decoration: none; font-weight: 600;">View in Admin</a>
            &nbsp;&middot;&nbsp;
            <a href="{{ route('home') }}" style="color: rgba(255,255,255,0.5); text-decoration: none;">mouse28.com</a>
        </p>
    @endslot
@endcomponent
