@component('emails.layout', ['logo' => true])
    @slot('header', null, ['style' => 'background: linear-gradient(135deg, #1a1040 0%, #2d1b69 60%, #3d2580 100%); border-radius: 16px 16px 0 0; padding: 36px 40px; text-align: center;'])
        <p style="margin: 0 0 12px; font-size: 36px; line-height: 1;">✨</p>
        <h1 style="margin: 0; font-family: 'Besley', Georgia, serif; font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.3;">Thanks for reaching out!</h1>
        <p style="margin: 8px 0 0; font-size: 14px; color: rgba(255,255,255,0.6);">We received your message and will get back to you soon.</p>
    @endslot

    <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #2a2040;">
        Hi there,
    </p>

    <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #2a2040;">
        Thanks for getting in touch with us at Mouse28! We've received your message and will do our best to respond within 48 hours.
    </p>

    <p style="margin: 0 0 24px; font-size: 13px; line-height: 1.7; color: #705f41;">
        If you didn't use the contact form on mouse28.com, you can ignore this email.
    </p>

    <p style="margin: 0 0 28px; font-size: 15px; line-height: 1.7; color: #2a2040;">
        In the meantime, check out our latest episodes and blog posts!
    </p>

    {{-- CTA Buttons --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ route('episodes.index') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #2d1b69, #1a1040); color: #ffffff; font-size: 14px; font-weight: 600; padding: 14px 32px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em; margin: 0 6px;">
                    🎙️&nbsp;&nbsp;Listen Now
                </a>
                <a href="{{ route('blog.index') }}"
                   style="display: inline-block; background: transparent; color: #2d1b69; font-size: 14px; font-weight: 600; padding: 12px 32px; border-radius: 10px; text-decoration: none; letter-spacing: 0.02em; border: 2px solid #2d1b69; margin: 0 6px;">
                    📖&nbsp;&nbsp;Read Blog
                </a>
            </td>
        </tr>
    </table>

    @slot('footer', null, ['style' => 'background: #1a1040; border-radius: 0 0 16px 16px; padding: 24px 40px; text-align: center;'])
        <p style="margin: 0 0 8px; font-size: 13px; color: rgba(255,255,255,0.5);">
            Jeffrey &amp; Cassie Davidson
        </p>
        <p style="margin: 0; font-size: 12px; color: rgba(255,255,255,0.35);">
            <a href="{{ route('home') }}" style="color: #d4a843; text-decoration: none;">mouse28.com</a>
        </p>
    @endslot
@endcomponent
