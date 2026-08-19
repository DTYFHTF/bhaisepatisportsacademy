<x-filament-panels::page>
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- ============ Catalog (left, 2/3) ============ --}}
        <div class="lg:col-span-2 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($this->categories as $cat)
                    <x-filament::button
                        :color="$category === $cat->value ? 'primary' : 'gray'"
                        size="sm"
                        wire:click="$set('category', '{{ $cat->value }}')"
                    >
                        {{ $cat->getLabel() }}
                    </x-filament::button>
                @endforeach

                <div class="ms-auto w-48">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                        <x-filament::input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search…"
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
                @forelse ($this->products as $product)
                    <button
                        type="button"
                        wire:click="addToCart('{{ $product->id }}')"
                        @class([
                            'rounded-lg bg-white p-3 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-500 dark:bg-gray-900 dark:ring-white/10',
                            'opacity-50' => $product->track_stock && $product->stock_on_hand < 1,
                        ])
                    >
                        <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $product->name }}
                        </div>
                        <div class="mt-0.5 flex items-baseline justify-between text-xs">
                            <span class="font-medium text-gray-950 dark:text-white">
                                {{ \App\Support\Money::npr($product->price) }}
                            </span>
                            @if ($product->member_price !== null)
                                <span class="text-success-600 dark:text-success-400">
                                    M: {{ \App\Support\Money::npr($product->member_price) }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-xs text-gray-500">
                            @if ($product->track_stock)
                                <span @class(['text-danger-600 dark:text-danger-400 font-semibold' => $product->isLowStock()])>
                                    {{ $product->stock_on_hand }} {{ $product->unit }}(s)
                                </span>
                            @else
                                per {{ $product->unit }}
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-8 text-center text-sm text-gray-500">
                        No products in this category.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ Cart (right, 1/3) ============ --}}
        <div class="space-y-3">
            {{-- Member attach --}}
            <div class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                @if ($this->member)
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $this->member->full_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $this->member->member_code }}
                                · <x-filament::badge :color="$this->member->status->getColor()" size="sm">
                                    {{ $this->member->status->getLabel() }}
                                </x-filament::badge>
                            </div>
                        </div>
                        <x-filament::button color="gray" size="xs" wire:click="detachMember">
                            Detach
                        </x-filament::button>
                    </div>
                @else
                    <x-filament::input.wrapper prefix-icon="heroicon-m-user">
                        <x-filament::input
                            type="text"
                            wire:model.live.debounce.300ms="memberSearch"
                            placeholder="Attach member (phone / code / name)"
                        />
                    </x-filament::input.wrapper>
                    @foreach ($this->memberResults as $member)
                        <button
                            type="button"
                            wire:click="attachMember('{{ $member->id }}')"
                            class="mt-1 flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <span class="truncate font-medium text-gray-950 dark:text-white">{{ $member->full_name }}</span>
                            <span class="text-xs text-gray-500">{{ $member->member_code }}</span>
                        </button>
                    @endforeach
                @endif
            </div>

            {{-- Cart lines --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="max-h-72 overflow-y-auto p-3">
                    @forelse ($this->cartItems as $item)
                        <div class="flex items-center gap-2 border-b border-gray-100 py-1.5 last:border-0 dark:border-white/5">
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm text-gray-950 dark:text-white">
                                    {{ $item['product']->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \App\Support\Money::npr($item['unit_price']) }}
                                    @if ($item['member_discount'] > 0)
                                        <span class="text-success-600 dark:text-success-400">(member)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon-button
                                    icon="heroicon-m-minus"
                                    size="xs"
                                    color="gray"
                                    wire:click="decrement('{{ $item['product']->id }}')"
                                    label="Less"
                                />
                                <span class="w-5 text-center text-sm font-semibold tabular-nums text-gray-950 dark:text-white">
                                    {{ $item['quantity'] }}
                                </span>
                                <x-filament::icon-button
                                    icon="heroicon-m-plus"
                                    size="xs"
                                    color="gray"
                                    wire:click="addToCart('{{ $item['product']->id }}')"
                                    label="More"
                                />
                            </div>
                            <div class="w-20 text-right text-sm font-semibold tabular-nums text-gray-950 dark:text-white">
                                {{ \App\Support\Money::npr($item['line_total']) }}
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-gray-500">
                            Tap products to add them.
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-gray-100 p-3 dark:border-white/10">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm text-gray-500">Total</span>
                        <span class="text-xl font-bold tabular-nums text-gray-950 dark:text-white">
                            {{ \App\Support\Money::npr($this->cartTotal) }}
                        </span>
                    </div>
                    @if ($this->memberSavings > 0)
                        <div class="mt-0.5 text-right text-xs text-success-600 dark:text-success-400">
                            Member saving {{ \App\Support\Money::npr($this->memberSavings) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment buttons --}}
            <div class="grid grid-cols-2 gap-2">
                <x-filament::button color="success" wire:click="checkout('cash')" icon="heroicon-m-banknotes">
                    Cash
                </x-filament::button>
                <x-filament::button color="info" wire:click="checkout('card')" icon="heroicon-m-credit-card">
                    Card
                </x-filament::button>
                <x-filament::button color="success" outlined wire:click="checkout('esewa')">
                    eSewa
                </x-filament::button>
                <x-filament::button color="info" outlined wire:click="checkout('khalti')">
                    Khalti
                </x-filament::button>
                @if ($this->member)
                    <x-filament::button
                        color="warning"
                        class="col-span-2"
                        wire:click="checkout('account')"
                        icon="heroicon-m-book-open"
                    >
                        On {{ $this->member->first_name }}'s account
                    </x-filament::button>
                @endif
                <x-filament::button color="gray" outlined class="col-span-2" wire:click="clearCart">
                    Clear cart
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
