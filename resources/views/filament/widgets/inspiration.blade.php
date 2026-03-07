<x-filament-widgets::widget>
    <div style="background: linear-gradient(135deg, rgba(26, 16, 64, 0.95), rgba(45, 27, 105, 0.9)); border: 1px solid rgba(212, 168, 67, 0.15); border-radius: 1.25rem; padding: 1.5rem; position: relative; overflow: hidden; height: 100%;">
        {{-- Decorative shimmer --}}
        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(240, 199, 94, 0.08), transparent 70%); pointer-events: none;"></div>

        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
            <span style="font-size: 1.1rem;">✨</span>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #fef9ef; margin: 0;">Writing Prompt</h3>
            <div style="flex: 1; height: 1px; background: linear-gradient(90deg, rgba(212, 168, 67, 0.3), transparent); margin-left: 0.5rem;"></div>
        </div>

        {{-- Quote --}}
        <div style="padding: 1rem; background: rgba(212, 168, 67, 0.06); border-radius: 0.75rem; border-left: 3px solid rgba(212, 168, 67, 0.4);">
            <p style="font-family: 'Playfair Display', serif; font-size: 1.05rem; font-style: italic; color: rgba(254, 249, 239, 0.9); margin: 0; line-height: 1.5;">
                "{{ $this->getPrompt() }}"
            </p>
        </div>

        <p style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: rgba(240, 199, 94, 0.5); margin: 0.75rem 0 0 0; text-align: right;">Refreshes on each visit</p>
    </div>
</x-filament-widgets::widget>
