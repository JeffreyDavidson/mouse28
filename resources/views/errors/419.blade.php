@extends('layouts.error')

@section('title', 'Page Expired — Mouse28')
@section('meta_description', 'Your Mouse28 session expired. Return to the form and try your request again.')
@section('og_title', 'Page Expired — Mouse28')
@section('og_description', 'Your Mouse28 session expired. Return to the form and try your request again.')

@section('content')
    <x-error-state code="419" eyebrow="Page expired" title="Your session took a break" message="For your security, the form expired before it was submitted. Return to the contact page and try again.">
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('contact.show') }}" class="inline-flex min-h-12 items-center rounded-full bg-gold px-6 py-3 font-semibold text-navy transition-colors hover:bg-gold-light">Return to contact</a>
            <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Go home</a>
        </div>
    </x-error-state>
@endsection
