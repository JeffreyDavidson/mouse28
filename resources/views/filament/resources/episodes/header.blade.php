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
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
            </div>
            <div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #f0c75e; margin: 0;">
                    Episodes
                </h2>
                <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.6); font-size: 0.85rem; margin: 0.25rem 0 0;">
                    Manage your podcast episodes
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="display: flex; gap: 0.75rem;">
                <div style="
                    background: rgba(91, 62, 158, 0.3);
                    border: 1px solid rgba(91, 62, 158, 0.4);
                    border-radius: 0.75rem;
                    padding: 0.5rem 1.25rem;
                    text-align: center;
                ">
                    <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fef9ef;">{{ $published }}</span>
                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: rgba(254, 249, 239, 0.6); display: block;">published</span>
                </div>
                @if($drafts > 0)
                    <div style="
                        background: rgba(212, 168, 67, 0.12);
                        border: 1px solid rgba(212, 168, 67, 0.25);
                        border-radius: 0.75rem;
                        padding: 0.5rem 1.25rem;
                        text-align: center;
                    ">
                        <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #f0c75e;">{{ $drafts }}</span>
                        <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: rgba(240, 199, 94, 0.7); display: block;">{{ str('draft')->plural($drafts) }}</span>
                    </div>
                @endif
            </div>

            <a href="{{ $createUrl }}" style="
                display: inline-flex; align-items: center; gap: 0.5rem;
                background: linear-gradient(135deg, #d4a843, #b8922e);
                color: #1a1040;
                font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 0.85rem;
                padding: 0.6rem 1.25rem; border-radius: 0.75rem;
                text-decoration: none; transition: all 0.3s ease;
            " onmouseover="this.style.background='linear-gradient(135deg, #f0c75e, #d4a843)';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='linear-gradient(135deg, #d4a843, #b8922e)';this.style.transform='translateY(0)'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                New Episode
            </a>
        </div>
    </div>
</div>
