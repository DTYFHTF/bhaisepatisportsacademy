<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Department;
use App\Models\Member;
use App\Services\EligibilityService;
use App\Support\Money;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $eligibility = app(EligibilityService::class);

        return $infolist->schema([
            Section::make()
                ->compact()
                ->schema([
                    Grid::make(6)->schema([
                        ImageEntry::make('photo_url')
                            ->label('')
                            ->circular()
                            ->size(72)
                            ->defaultImageUrl('https://ui-avatars.com/api/?background=0d9488&color=fff&size=128'),
                        TextEntry::make('member_code')->label('Code')->badge()->color('gray')->copyable(),
                        TextEntry::make('full_name')->label('Name')
                            ->state(fn (Member $record) => $record->full_name)
                            ->weight('bold'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('phone')->icon('heroicon-m-phone')->copyable(),
                        TextEntry::make('outstanding')
                            ->label('Outstanding dues')
                            ->state(fn (Member $record) => $record->outstandingBalance())
                            ->formatStateUsing(fn (int $state) => Money::npr($state))
                            ->color(fn (int $state) => $state > 0 ? 'danger' : 'success')
                            ->weight('bold'),
                    ]),
                ]),

            Section::make('Eligibility right now')
                ->description('Live answer per department — the same check the door hardware runs.')
                ->compact()
                ->schema([
                    Grid::make(5)->schema(
                        Department::active()->accessControlled()->orderBy('sort_order')->get()
                            ->map(fn (Department $d) => TextEntry::make("eligibility_{$d->code}")
                                ->label($d->name)
                                ->state(function (Member $record) use ($eligibility, $d) {
                                    $result = $eligibility->check($record, $d);

                                    return $result->allowed ? 'Allowed' : $result->reason->getLabel();
                                })
                                ->badge()
                                ->color(fn (string $state) => $state === 'Allowed' ? 'success' : 'danger'))
                            ->all(),
                    ),
                ]),

            Section::make('Profile')
                ->compact()
                ->collapsed()
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('date_of_birth')->date('j M Y')
                            ->helperText(fn (Member $record) => $record->age !== null ? "{$record->age} years" : null),
                        TextEntry::make('gender')->badge()->color('gray'),
                        TextEntry::make('blood_group'),
                        TextEntry::make('email')->placeholder('—'),
                        TextEntry::make('address')
                            ->state(fn (Member $record) => collect([
                                $record->tole,
                                $record->ward_no ? "Ward {$record->ward_no}" : null,
                                $record->municipality,
                                $record->district,
                            ])->filter()->implode(', ') ?: '—'),
                        TextEntry::make('occupation')->placeholder('—'),
                        TextEntry::make('emergency_contact_name')
                            ->label('Emergency contact')
                            ->state(fn (Member $record) => $record->emergency_contact_name
                                ? "{$record->emergency_contact_name} ({$record->emergency_contact_relation}) {$record->emergency_contact_phone}"
                                : '—'),
                        TextEntry::make('guardian_name')
                            ->label('Guardian')
                            ->state(fn (Member $record) => $record->guardian_name
                                ? "{$record->guardian_name} ({$record->guardian_relation}) {$record->guardian_phone}"
                                : '—'),
                        TextEntry::make('medical_conditions')->placeholder('None')->columnSpan(2),
                        TextEntry::make('allergies')->placeholder('None')->columnSpan(2),
                        TextEntry::make('referral_source')->badge()->color('gray')->placeholder('—'),
                        TextEntry::make('joined_on')->date('j M Y'),
                    ]),
                ]),
        ]);
    }
}
