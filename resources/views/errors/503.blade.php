@extends('layouts.error')

@section('title', 'We’ll Be Right Back — Mouse28')
@section('meta_description', 'Mouse28 is temporarily unavailable while we make an update. Please try again shortly.')
@section('og_title', 'We’ll Be Right Back — Mouse28')
@section('og_description', 'Mouse28 is temporarily unavailable while we make an update. Please try again shortly.')

@section('content')
    <x-error-state code="503" eyebrow="Quick maintenance break" title="We’re making a little magic" message="Mouse28 is temporarily unavailable while we make an update. Please try this page again shortly.">
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ url()->current() }}" class="inline-flex min-h-12 items-center rounded-full bg-gold px-6 py-3 font-semibold text-navy transition-colors hover:bg-gold-light">Try again</a>
            <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Go home</a>
        </div>
    </x-error-state>
@endsection
