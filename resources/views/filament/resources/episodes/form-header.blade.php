<div style="
    background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%);
    border-radius: 1.25rem;
    padding: 1.75rem 2.5rem;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(212, 168, 67, 0.2);
    margin-bottom: 1.5rem;
">
    <div style="position: absolute; top: -30%; right: -10%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(212, 168, 67, 0.1) 0%, transparent 70%); pointer-events: none;"></div>
    <div style="position: absolute; top: 20%; right: 15%; color: rgba(240, 199, 94, 0.2); font-size: 0.8rem;">✦</div>

    <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 1.25rem;">
        <div style="
            width: 52px; height: 52px; border-radius: 0.875rem;
            background: rgba(212, 168, 67, 0.15);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
        </div>
        <div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #f0c75e; margin: 0;">
                {{ $title }}
            </h2>
            <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.6); font-size: 0.8rem; margin: 0.2rem 0 0;">
                {{ $subtitle }}
            </p>
        </div>
    </div>
</div>
