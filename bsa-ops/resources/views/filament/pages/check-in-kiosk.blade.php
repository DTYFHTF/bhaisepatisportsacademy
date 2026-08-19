<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Search / scan --}}
        <div class="max-w-xl">
            <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Scan card / QR, or search phone, code, name…"
                    autofocus
                />
            </x-filament::input.wrapper>
        </div>

        {{-- Search results --}}
        @if ($this->results->isNotEmpty())
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($this->results as $member)
                    <button
                        type="button"
                        wire:click="select('{{ $member->id }}')"
                        class="flex items-center gap-3 rounded-lg bg-white p-3 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-500 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <img
                            src="{{ $member->photo_url ?: 'https://ui-avatars.com/api/?background=0d9488&color=fff&name=' . urlencode($member->full_name) }}"
                            class="h-10 w-10 rounded-full object-cover"
                            alt=""
                        />
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $member->full_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $member->member_code }} · {{ $member->phone }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Selected member --}}
        @if ($this->selectedMember)
            @php($member = $this->selectedMember)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex flex-wrap items-center gap-4">
                    <img
                        src="{{ $member->photo_url ?: 'https://ui-avatars.com/api/?background=0d9488&color=fff&size=128&name=' . urlencode($member->full_name) }}"
                        class="h-16 w-16 rounded-full object-cover"
                        alt=""
                    />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-lg font-bold text-gray-950 dark:text-white">{{ $member->full_name }}</span>
                            <x-filament::badge color="gray">{{ $member->member_code }}</x-filament::badge>
                            <x-filament::badge :color="$member->status->getColor()">{{ $member->status->getLabel() }}</x-filament::badge>
                        </div>
                        <div class="mt-0.5 text-sm text-gray-500">
                            {{ $member->phone }}
                            @if ($member->outstandingBalance() > 0)
                                · <span class="font-semibold text-danger-600 dark:text-danger-400">
                                    Dues {{ \App\Support\Money::npr($member->outstandingBalance()) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <x-filament::button color="gray" size="sm" wire:click="clearSelection">
                        Clear
                    </x-filament::button>
                </div>

                {{-- Per-department eligibility + one-tap check-in --}}
                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ($this->departments as $dept)
                        <button
                            type="button"
                            wire:click="checkIn('{{ $dept['model']->id }}')"
                            @class([
                                'rounded-lg p-3 text-left ring-1 transition',
                                'bg-success-50 ring-success-300 hover:bg-success-100 dark:bg-success-500/10 dark:ring-success-500/40' => $dept['allowed'],
                                'bg-danger-50 ring-danger-300 opacity-80 dark:bg-danger-500/10 dark:ring-danger-500/40' => ! $dept['allowed'],
                            ])
                        >
                            <div class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $dept['model']->name }}
                            </div>
                            <div @class([
                                'mt-0.5 text-xs font-medium',
                                'text-success-700 dark:text-success-400' => $dept['allowed'],
                                'text-danger-700 dark:text-danger-400' => ! $dept['allowed'],
                            ])>
                                {{ $dept['allowed'] ? 'Tap to check in' : $dept['reason'] }}
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
