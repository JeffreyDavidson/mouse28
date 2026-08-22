@extends('layouts.error')

@section('title', 'Something Went Wrong — Mouse28')
@section('meta_description', 'Mouse28 could not complete this request. Please try again in a moment.')
@section('og_title', 'Something Went Wrong — Mouse28')
@section('og_description', 'Mouse28 could not complete this request. Please try again in a moment.')

@section('content')
    <x-error-state code="500" eyebrow="Unexpected error" title="The magic hit a snag" message="Something unexpected happened while loading this page. Please try again in a moment.">
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ url()->current() }}" class="inline-flex min-h-12 items-center rounded-full bg-gold px-6 py-3 font-semibold text-navy transition-colors hover:bg-gold-light">Try again</a>
            <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Go home</a>
        </div>
    </x-error-state>
@endsection
