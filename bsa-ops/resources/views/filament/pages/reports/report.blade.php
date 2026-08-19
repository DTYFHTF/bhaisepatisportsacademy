<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="max-w-lg flex-1">
                {{ $this->form }}
            </div>
            <x-filament::button icon="heroicon-m-arrow-down-tray" wire:click="export">
                Export CSV
            </x-filament::button>
        </div>

        @php($report = $this->report)
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left dark:border-white/10">
                        @foreach ($report['headers'] as $header)
                            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                            @foreach ($row as $cell)
                                <td class="whitespace-nowrap px-3 py-1.5 tabular-nums text-gray-950 dark:text-white">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['headers']) }}" class="px-3 py-6 text-center text-gray-500">
                                No data in this range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
