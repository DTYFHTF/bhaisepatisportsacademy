{{-- Styles live in resources/css/filament/ops-theme.css (plain CSS, so they
     do not depend on a Tailwind build step for this custom view). --}}
<div class="bsa-qa-grid">
    @foreach ($this->getActions() as $action)
        <a href="{{ $action['url'] }}" class="bsa-qa-tile" data-tone="{{ $action['color'] }}">
            @if (filled($action['count']))
                <span class="bsa-qa-badge">{{ $action['count'] }}</span>
            @endif

            <span class="bsa-qa-icon">
                <x-filament::icon :icon="$action['icon']" />
            </span>

            <span class="bsa-qa-label">{{ $action['label'] }}</span>
            <span class="bsa-qa-hint">{{ $action['hint'] }}</span>
        </a>
    @endforeach
</div>
