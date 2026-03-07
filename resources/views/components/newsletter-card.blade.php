<div class="bg-gradient-to-br from-navy via-navy-light to-navy rounded-2xl p-7 text-center relative overflow-hidden border border-white/5">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gold/5 rounded-full blur-3xl"></div>
    <div class="relative">
        <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-white/10 flex items-center justify-center border border-white/10">
            <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        </div>
        <h3 class="font-heading text-lg font-bold text-white mb-2">Stay in the Loop</h3>
        <p class="text-white/40 text-sm mb-5 leading-relaxed">{{ $subtitle ?? 'New posts & Disney tips delivered to your inbox' }}</p>
        <form action="/newsletter" method="POST" class="space-y-3">
            @csrf
            <input type="email" name="email" placeholder="your@email.com" required
                class="w-full px-4 py-3 text-sm rounded-xl outline-none transition-all placeholder:text-white/25 text-white"
                style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);"
                onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(255,255,255,0.12)'"
                onblur="this.style.borderColor='rgba(255,255,255,0.1)';this.style.background='rgba(255,255,255,0.08)'"
            >
            <button type="submit" class="w-full bg-gradient-to-r from-gold to-gold-light text-navy font-bold text-sm py-3 rounded-full transition-all hover:-translate-y-0.5 shadow-lg shadow-gold/20">
                Subscribe
            </button>
        </form>
        <p class="text-white/20 text-[10px] mt-3">No spam. Unsubscribe anytime.</p>
    </div>
</div>
