{{-- A publication date shown in the site's display timezone; without a date it shows the fallback, if any. --}}
@props(['date', 'format' => 'F j, Y', 'fallback' => null])
@if ($date)
    @php($displayDate = \JeffreyDavidson\CreatorKit\Support\Time\DisplayTimezone::convert($date))
    <time {{ $attributes->merge(['datetime' => $displayDate->toDateString()]) }}>{{ $displayDate->format($format) }}</time>
@elseif ($fallback)
    <span {{ $attributes }}>{{ $fallback }}</span>
@endif
