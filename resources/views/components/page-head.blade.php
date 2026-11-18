@props(['title', 'kicker' => null, 'description' => null, 'crumbs' => []])
<div class="t-page-head">
    <div>
        @if ($crumbs)
            <ol class="t-crumbs">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                @foreach ($crumbs as $label => $url)
                    <li>@if ($url)<a href="{{ $url }}">{{ $label }}</a>@else{{ $label }}@endif</li>
                @endforeach
            </ol>
        @elseif ($kicker)
            <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> {{ $kicker }}</span>
        @endif
        <h1>{{ $title }}</h1>
        @if ($description)<p>{{ $description }}</p>@endif
    </div>
    @if (trim($slot))
        <div class="t-actions">{{ $slot }}</div>
    @endif
</div>
