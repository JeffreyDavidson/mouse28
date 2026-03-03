<x-filament-widgets::widget>
    <div style="
        background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%);
        border-radius: 1.25rem;
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(212, 168, 67, 0.2);
    ">
        {{-- Decorative glow --}}
        <div style="
            position: absolute;
            top: -30%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 168, 67, 0.15) 0%, transparent 70%);
            pointer-events: none;
        "></div>

        {{-- Stars --}}
        <div style="position: absolute; top: 15%; right: 15%; color: rgba(240, 199, 94, 0.3); font-size: 0.8rem;">✦</div>
        <div style="position: absolute; top: 60%; right: 25%; color: rgba(240, 199, 94, 0.2); font-size: 0.6rem;">✦</div>
        <div style="position: absolute; bottom: 20%; right: 10%; color: rgba(240, 199, 94, 0.25); font-size: 1rem;">✦</div>

        <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <h2 style="
                    font-family: 'Playfair Display', serif;
                    font-size: 1.75rem;
                    font-weight: 700;
                    color: #f0c75e;
                    margin: 0 0 0.5rem;
                ">
                    Welcome back, {{ auth()->user()->name }} ✨
                </h2>
                <p style="
                    font-family: 'Poppins', sans-serif;
                    color: rgba(254, 249, 239, 0.7);
                    margin: 0;
                    font-size: 0.9rem;
                ">
                    @php
                        $hour = now('America/New_York')->hour;
                        $greeting = match(true) {
                            $hour < 12 => 'Good morning!',
                            $hour < 17 => 'Good afternoon!',
                            default => 'Good evening!',
                        };
                        $postCount = \App\Models\Post::where('is_published', false)->count();
                        $storyCount = 0; // Community Stories disabled
                    @endphp
                    {{ $greeting }}
                    @if ($postCount > 0 || $storyCount > 0)
                        You have
                        @if ($postCount > 0) {{ $postCount }} draft {{ str('post')->plural($postCount) }} @endif
                        @if ($postCount > 0 && $storyCount > 0) and @endif
                        @if ($storyCount > 0) {{ $storyCount }} {{ str('story')->plural($storyCount) }} awaiting review @endif
                    @else
                        Ready to create something magical?
                    @endif
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('filament.admin.resources.posts.create') }}"
                   style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        background: linear-gradient(135deg, #d4a843, #b8922e);
                        color: #1a1040;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 600;
                        font-size: 0.85rem;
                        padding: 0.6rem 1.25rem;
                        border-radius: 0.75rem;
                        text-decoration: none;
                        transition: all 0.3s ease;
                   "
                   onmouseover="this.style.background='linear-gradient(135deg, #f0c75e, #d4a843)';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.background='linear-gradient(135deg, #d4a843, #b8922e)';this.style.transform='translateY(0)'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Post
                </a>
                <a href="{{ route('filament.admin.resources.episodes.create') }}"
                   style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        background: rgba(254, 249, 239, 0.1);
                        color: #fef9ef;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 500;
                        font-size: 0.85rem;
                        padding: 0.6rem 1.25rem;
                        border-radius: 0.75rem;
                        border: 1px solid rgba(212, 168, 67, 0.3);
                        text-decoration: none;
                        transition: all 0.3s ease;
                   "
                   onmouseover="this.style.background='rgba(254, 249, 239, 0.15)';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.background='rgba(254, 249, 239, 0.1)';this.style.transform='translateY(0)'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                    New Episode
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
