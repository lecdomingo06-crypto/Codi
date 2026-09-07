@props(['difficulty'])

<span {{ $attributes->merge(['class' => 'badge difficulty-'.strtolower($difficulty)]) }}>{{ ucfirst(strtolower($difficulty)) }}</span>
