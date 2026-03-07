<div style="
    background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%);
    border-radius: 1.25rem;
    padding: 2rem 2.5rem;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(212, 168, 67, 0.2);
    margin-bottom: 1.5rem;
">
    <div style="position: absolute; top: -30%; right: -10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(212, 168, 67, 0.12) 0%, transparent 70%); pointer-events: none;"></div>
    <div style="position: absolute; top: 15%; right: 18%; color: rgba(240, 199, 94, 0.25); font-size: 0.8rem;">✦</div>
    <div style="position: absolute; bottom: 20%; right: 10%; color: rgba(240, 199, 94, 0.2); font-size: 1rem;">✦</div>

    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="
                width: 64px; height: 64px; border-radius: 1rem;
                background: rgba(212, 168, 67, 0.15);
                display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            ">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #f0c75e; margin: 0;">
                    Contact Messages
                </h2>
                <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.6); font-size: 0.85rem; margin: 0.25rem 0 0;">
                    Messages from your site visitors
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            {{-- Total badge --}}
            <div style="
                background: rgba(91, 62, 158, 0.3);
                border: 1px solid rgba(91, 62, 158, 0.4);
                border-radius: 0.75rem;
                padding: 0.5rem 1.25rem;
                text-align: center;
            ">
                <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fef9ef;">{{ $total }}</span>
                <span style="font-family: 'Poppins', sans-serif; font-size: 0.75rem; color: rgba(254, 249, 239, 0.6); display: block;">total</span>
            </div>

            {{-- Unread badge --}}
            @if($unread > 0)
                <div style="
                    background: rgba(212, 168, 67, 0.12);
                    border: 1px solid rgba(212, 168, 67, 0.25);
                    border-radius: 0.75rem;
                    padding: 0.5rem 1.25rem;
                    text-align: center;
                ">
                    <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #f0c75e;">{{ $unread }}</span>
                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.75rem; color: rgba(240, 199, 94, 0.7); display: block;">unread</span>
                </div>
            @endif
        </div>
    </div>
</div>
