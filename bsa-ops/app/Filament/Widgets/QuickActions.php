<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Filament\Pages\CheckInKiosk;
use App\Filament\Pages\PosTerminal;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\MemberResource;
use App\Filament\Resources\MemberSubscriptionResource;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MemberSubscription;
use Filament\Widgets\Widget;

/**
 * The things front-desk staff do all day, one tap from the dashboard.
 * Built for a phone in someone's hand at the counter.
 */
class QuickActions extends Widget
{
    protected static ?int $sort = 0;

    protected static string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActions(): array
    {
        return [
            [
                'label' => 'Check in',
                'hint' => 'Scan or search',
                'icon' => 'heroicon-o-bolt',
                'url' => CheckInKiosk::getUrl(),
                'color' => 'success',
                'count' => null,
            ],
            [
                'label' => 'Sell',
                'hint' => 'Kitchen & shop',
                'icon' => 'heroicon-o-shopping-cart',
                'url' => PosTerminal::getUrl(),
                'color' => 'warning',
                'count' => null,
            ],
            [
                'label' => 'New member',
                'hint' => 'Sign someone up',
                'icon' => 'heroicon-o-user-plus',
                'url' => MemberResource::getUrl('create'),
                'color' => 'primary',
                'count' => null,
            ],
            [
                'label' => 'Collect dues',
                'hint' => 'Unpaid invoices',
                'icon' => 'heroicon-o-banknotes',
                'url' => InvoiceResource::getUrl('index', ['activeTab' => 'outstanding']),
                'color' => 'danger',
                'count' => Invoice::query()->outstanding()->count(),
            ],
            [
                'label' => 'Renewals',
                'hint' => 'Expiring in 14 days',
                'icon' => 'heroicon-o-arrow-path',
                'url' => MemberSubscriptionResource::getUrl('index', ['activeTab' => 'expiring']),
                'color' => 'info',
                'count' => MemberSubscription::query()->expiringWithin(14)->count(),
            ],
            [
                'label' => 'Members',
                'hint' => 'Search everyone',
                'icon' => 'heroicon-o-identification',
                'url' => MemberResource::getUrl('index'),
                'color' => 'gray',
                'count' => Member::query()->active()->count(),
            ],
        ];
    }
}
