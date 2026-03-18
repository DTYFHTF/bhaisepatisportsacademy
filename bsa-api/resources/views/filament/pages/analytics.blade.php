<x-filament-panels::page>
    @if(config('services.umami.share_url'))
        <iframe
            src="{{ config('services.umami.share_url') }}"
            scrolling="no"
            class="w-full"
            style="height: 1600px; border: none;"
        ></iframe>
    @else
        <div class="text-center py-12 text-gray-500">
            <p class="text-lg font-medium">Analytics not configured</p>
            <p class="mt-2">Set <code>UMAMI_SHARE_URL</code> in your <code>.env</code> to enable the analytics dashboard.</p>
        </div>
    @endif
</x-filament-panels::page>
