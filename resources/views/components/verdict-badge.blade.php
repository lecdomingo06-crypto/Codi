@props(['verdict'])

@php
    $state = match ($verdict) {
        'ACCEPTED' => 'accepted',
        'PENDING', 'QUEUED' => 'pending',
        default => 'failed',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge verdict verdict-'.$state]) }}>{{ str_replace('_', ' ', ucfirst(strtolower($verdict))) }}</span>
