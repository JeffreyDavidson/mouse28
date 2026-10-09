@props([
    'status',
    'title',
    'description',
    'heading',
    'message',
])

@php($recovery = \App\Support\ErrorRecovery::for(request(), $status))

<x-layouts.error :title="$title" :description="$description" :og-title="$title">
    <x-error-state :code="$status" :title="$heading" :message="$message">
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $recovery['url'] }}"
                class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >{{ $recovery['label'] }}</a>
            <a
                href="{{ route('home') }}"
                class="dispatch-error-secondary inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Go home</a>
        </div>
    </x-error-state>
</x-layouts.error>
