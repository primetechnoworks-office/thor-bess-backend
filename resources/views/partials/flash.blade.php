@foreach (['success' => 'check-circle', 'error' => 'alert', 'status' => 'check-circle'] as $key => $icon)
    @if (session($key))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.400ms
             class="t-alert t-toast t-alert--{{ $key === 'error' ? 'error' : 'success' }}" role="status">
            <x-icon :name="$icon" />
            <span>{{ session($key) }}</span>
            <button type="button" class="t-alert__close" @click="show = false" aria-label="Dismiss"><x-icon name="x" size="18" /></button>
        </div>
    @endif
@endforeach
