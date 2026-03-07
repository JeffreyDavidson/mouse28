<x-filament-widgets::widget>
    <div style="background: linear-gradient(135deg, rgba(26, 16, 64, 0.95), rgba(45, 27, 105, 0.9)); border: 1px solid rgba(212, 168, 67, 0.15); border-radius: 1.25rem; padding: 1.5rem; position: relative; overflow: hidden; height: 100%;">
        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
            <span style="font-size: 1.1rem;">✏️</span>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #fef9ef; margin: 0;">Quick Draft</h3>
            <div style="flex: 1; height: 1px; background: linear-gradient(90deg, rgba(212, 168, 67, 0.3), transparent); margin-left: 0.5rem;"></div>
        </div>

        <form wire:submit.prevent="saveDraft">
            {{ $this->form }}

            <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                <button type="submit" style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; font-weight: 600; color: #1a1040; background: linear-gradient(135deg, #d4a843, #f0c75e); border: none; border-radius: 0.75rem; padding: 0.6rem 1.25rem; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                    Save Draft
                </button>
            </div>
        </form>
    </div>
</x-filament-widgets::widget>
