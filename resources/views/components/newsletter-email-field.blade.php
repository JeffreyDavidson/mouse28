@props(['inputId', 'errorId', 'inputClass', 'errorClass'])

@php
    $newsletterErrors = $errors->getBag('newsletter');
    $hasFeedback = $newsletterErrors->isNotEmpty() || session('newsletter_error');
@endphp

<label for="{{ $inputId }}" class="sr-only">Email address</label>
<input
    id="{{ $inputId }}"
    type="email"
    name="email"
    value="{{ $hasFeedback ? old('email') : '' }}"
    placeholder="your@email.com"
    autocomplete="email"
    required
    @if ($newsletterErrors->has('email')) aria-invalid="true" aria-describedby="{{ $errorId }}" autofocus @endif
    class="{{ $inputClass }}"
/>
@if ($newsletterErrors->has('email'))
    <p id="{{ $errorId }}" role="alert" class="{{ $errorClass }}">{{ $newsletterErrors->first('email') }}</p>
@endif
