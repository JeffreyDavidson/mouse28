@extends('layouts.app')

@section('title', 'Community Stories — Mouse28')
@section('meta_description', 'Real stories from autism families sharing their Disney park experiences.')

@section('content')
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-purple py-16 sm:py-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-4xl sparkle">✨</div>
            <div class="absolute top-20 right-20 text-3xl sparkle-delay">⭐</div>
            <div class="absolute bottom-16 left-1/3 text-2xl sparkle-delay-2">✨</div>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <h1 class="font-heading text-4xl sm:text-5xl font-bold text-white mb-4">Our Disney Family</h1>
            <p class="text-lg text-white/70 max-w-2xl mx-auto leading-relaxed">Real stories from real families navigating the magic of Disney with children on the autism spectrum. You're not alone on this journey.</p>
            <a href="/stories/share" class="inline-block mt-8 bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">
                Share Your Story
            </a>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
        {{-- Featured Stories --}}
        @if($featured->isNotEmpty())
            <section class="mb-16">
                <h2 class="font-heading text-2xl font-bold text-navy mb-8">Featured Stories</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($featured as $story)
                        <div class="bg-white rounded-2xl shadow-sm border border-cream-dark p-6 relative">
                            <div class="absolute top-4 right-4 text-gold text-2xl">⭐</div>
                            <div class="text-4xl text-purple/20 font-heading leading-none mb-2">"</div>
                            <h3 class="font-heading text-lg font-bold text-navy mb-2">{{ $story->title }}</h3>
                            <p class="text-navy/70 text-sm leading-relaxed mb-4">{{ Str::limit($story->story, 200) }}</p>
                            <div class="border-t border-cream-dark pt-4 mt-auto">
                                <p class="font-semibold text-navy text-sm">{{ $story->name }}</p>
                                @if($story->location)
                                    <p class="text-navy/50 text-xs">{{ $story->location }}</p>
                                @endif
                                @if($story->child_name)
                                    <p class="text-purple text-xs mt-1">
                                        {{ $story->child_name }}@if($story->child_age), age {{ $story->child_age }}@endif
                                    </p>
                                @endif
                                @if($story->favorite_park)
                                    <span class="inline-block mt-2 text-xs bg-purple/10 text-purple px-2 py-1 rounded-full">🏰 {{ $story->favorite_park }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- All Stories --}}
        @if($stories->isNotEmpty())
            <section>
                <h2 class="font-heading text-2xl font-bold text-navy mb-8">All Stories</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($stories as $story)
                        <div class="bg-white rounded-2xl shadow-sm border border-cream-dark p-6 hover:shadow-md transition-shadow">
                            <h3 class="font-heading text-lg font-bold text-navy mb-2">{{ $story->title }}</h3>
                            <p class="text-navy/70 text-sm leading-relaxed mb-4">{{ Str::limit($story->story, 150) }}</p>
                            <div class="border-t border-cream-dark pt-4">
                                <p class="font-semibold text-navy text-sm">{{ $story->name }}</p>
                                @if($story->location)
                                    <p class="text-navy/50 text-xs">{{ $story->location }}</p>
                                @endif
                                @if($story->child_name)
                                    <p class="text-purple text-xs mt-1">
                                        {{ $story->child_name }}@if($story->child_age), age {{ $story->child_age }}@endif
                                    </p>
                                @endif
                                @if($story->favorite_park)
                                    <span class="inline-block mt-2 text-xs bg-purple/10 text-purple px-2 py-1 rounded-full">🏰 {{ $story->favorite_park }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $stories->links() }}
                </div>
            </section>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🏰</div>
                <h2 class="font-heading text-2xl font-bold text-navy mb-2">Be the First to Share</h2>
                <p class="text-navy/60 max-w-md mx-auto mb-8">We'd love to hear about your family's Disney adventures. Every story helps another family feel less alone.</p>
                <a href="/stories/share" class="inline-block bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">
                    Share Your Story
                </a>
            </div>
        @endif
    </div>
@endsection
