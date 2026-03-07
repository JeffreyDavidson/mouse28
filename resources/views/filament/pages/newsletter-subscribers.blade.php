<x-filament-panels::page>
    @php
        $subscribers = $this->getSubscribers();
        $error = $this->getErrorMessage();
        $count = count($subscribers);
    @endphp

    {{-- Header Card --}}
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
        <div style="position: absolute; top: 15%; right: 12%; color: rgba(240, 199, 94, 0.25); font-size: 0.8rem;">✦</div>
        <div style="position: absolute; bottom: 25%; right: 8%; color: rgba(240, 199, 94, 0.2); font-size: 1rem;">✦</div>

        <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="
                    width: 64px; height: 64px; border-radius: 1rem;
                    background: rgba(212, 168, 67, 0.15);
                    display: flex; align-items: center; justify-content: center;
                ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19c-2.1 0-3.4-.6-4.3-1.2a5 5 0 0 1-1.7-2c-.2-.5-.3-1-.3-1.3V7a3 3 0 0 1 6 0v7.5c0 .3-.1.8-.3 1.3a5 5 0 0 1-1.7 2c-.9.6-2.2 1.2-4.3 1.2"/><path d="M12 6.5a3.5 3.5 0 0 0-7 0v5a3.5 3.5 0 0 0 7 0"/><path d="M16 3a7 7 0 0 1 0 14"/><rect x="2" y="2" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #f0c75e; margin: 0;">
                        Newsletter Subscribers
                    </h2>
                    <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.6); font-size: 0.85rem; margin: 0.25rem 0 0;">
                        Powered by Resend · Cached 5 min
                    </p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                {{-- Subscriber count badge --}}
                <div style="
                    background: rgba(212, 168, 67, 0.12);
                    border: 1px solid rgba(212, 168, 67, 0.25);
                    border-radius: 0.75rem;
                    padding: 0.5rem 1.25rem;
                    text-align: center;
                ">
                    <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fef9ef;">{{ $count }}</span>
                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.75rem; color: rgba(254, 249, 239, 0.6); display: block;">{{ str('subscriber')->plural($count) }}</span>
                </div>

                {{-- Export button --}}
                @if($count > 0)
                    <button wire:click="exportCsv" style="
                        display: inline-flex; align-items: center; gap: 0.5rem;
                        background: linear-gradient(135deg, #d4a843, #b8922e);
                        color: #1a1040;
                        font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 0.85rem;
                        padding: 0.6rem 1.25rem; border-radius: 0.75rem; border: none;
                        cursor: pointer; transition: all 0.3s ease;
                    " onmouseover="this.style.background='linear-gradient(135deg, #f0c75e, #d4a843)';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='linear-gradient(135deg, #d4a843, #b8922e)';this.style.transform='translateY(0)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Export CSV
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($error)
        <div style="
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <span style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #fca5a5;">{{ $error }}</span>
        </div>
    @endif

    @if($count === 0 && !$error)
        {{-- Empty state --}}
        <div style="
            background: rgba(45, 27, 105, 0.3);
            border: 1px solid rgba(212, 168, 67, 0.12);
            border-radius: 1.25rem;
            padding: 4rem 2rem;
            text-align: center;
        ">
            <div style="color: rgba(212, 168, 67, 0.25); margin-bottom: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: #fef9ef; margin: 0 0 0.5rem;">No subscribers yet</h3>
            <p style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: rgba(254, 249, 239, 0.5); margin: 0;">Share your newsletter signup link to start growing your audience ✨</p>
        </div>
    @elseif($count > 0)
        {{-- Subscriber table --}}
        <div style="
            background: rgba(45, 27, 105, 0.3);
            border: 1px solid rgba(212, 168, 67, 0.12);
            border-radius: 1.25rem;
            overflow: hidden;
        ">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(26, 16, 64, 0.6);">
                        <th style="
                            padding: 1rem 1.5rem; text-align: left;
                            font-family: 'Poppins', sans-serif; font-size: 0.7rem; font-weight: 600;
                            color: rgba(212, 168, 67, 0.8); text-transform: uppercase; letter-spacing: 0.08em;
                            border-bottom: 1px solid rgba(212, 168, 67, 0.1);
                        ">#</th>
                        <th style="
                            padding: 1rem 1.5rem; text-align: left;
                            font-family: 'Poppins', sans-serif; font-size: 0.7rem; font-weight: 600;
                            color: rgba(212, 168, 67, 0.8); text-transform: uppercase; letter-spacing: 0.08em;
                            border-bottom: 1px solid rgba(212, 168, 67, 0.1);
                        ">Email Address</th>
                        <th style="
                            padding: 1rem 1.5rem; text-align: left;
                            font-family: 'Poppins', sans-serif; font-size: 0.7rem; font-weight: 600;
                            color: rgba(212, 168, 67, 0.8); text-transform: uppercase; letter-spacing: 0.08em;
                            border-bottom: 1px solid rgba(212, 168, 67, 0.1);
                        ">Subscribed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscribers as $index => $subscriber)
                        <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(212, 168, 67, 0.04)'" onmouseout="this.style.background='transparent'">
                            <td style="
                                padding: 1rem 1.5rem;
                                font-family: 'Poppins', sans-serif; font-size: 0.8rem;
                                color: rgba(254, 249, 239, 0.4);
                                border-bottom: 1px solid rgba(212, 168, 67, 0.06);
                            ">{{ $index + 1 }}</td>
                            <td style="
                                padding: 1rem 1.5rem;
                                border-bottom: 1px solid rgba(212, 168, 67, 0.06);
                            ">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="
                                        width: 36px; height: 36px; border-radius: 50%;
                                        background: linear-gradient(135deg, #5b3e9e, #3a2370);
                                        display: flex; align-items: center; justify-content: center;
                                        font-family: 'Poppins', sans-serif; font-size: 0.75rem; font-weight: 600;
                                        color: #f0c75e; flex-shrink: 0;
                                    ">{{ strtoupper(substr($subscriber['email'] ?? '?', 0, 1)) }}</div>
                                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.9rem; color: #fef9ef;">{{ $subscriber['email'] ?? '—' }}</span>
                                </div>
                            </td>
                            <td style="
                                padding: 1rem 1.5rem;
                                font-family: 'Poppins', sans-serif; font-size: 0.85rem;
                                color: rgba(254, 249, 239, 0.5);
                                border-bottom: 1px solid rgba(212, 168, 67, 0.06);
                            ">
                                {{ isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at'])->format('M j, Y') : '—' }}
                                @if(isset($subscriber['created_at']))
                                    <span style="color: rgba(254, 249, 239, 0.3); font-size: 0.75rem; margin-left: 0.5rem;">
                                        {{ \Carbon\Carbon::parse($subscriber['created_at'])->format('g:ia') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
