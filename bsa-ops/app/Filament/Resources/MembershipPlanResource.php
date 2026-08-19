<?php

namespace App\Filament\Resources;

use App\Enums\IntervalUnit;
use App\Enums\PlanType;
use App\Filament\Resources\MembershipPlanResource\Pages;
use App\Models\MembershipPlan;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipPlanResource extends Resource
{
    protected static ?string $model = MembershipPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'plan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Plan')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('code')->required()->maxLength(30)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')->required()->columnSpan(2),
                        Forms\Components\Select::make('plan_type')
                            ->options(PlanType::class)
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\CheckboxList::make('departments')
                            ->relationship('departments', 'name')
                            ->required()
                            ->columns(3)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')->rows(2)->columnSpan(3),
                    ]),
                ]),
            Forms\Components\Section::make('Term')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Select::make('interval_unit')
                            ->options(IntervalUnit::class)
                            ->visible(fn (Forms\Get $get) => $get('plan_type') === PlanType::TimeBased->value || $get('plan_type') === PlanType::TimeBased)
                            ->native(false),
                        Forms\Components\TextInput::make('interval_count')->numeric()->minValue(1)
                            ->visible(fn (Forms\Get $get) => $get('plan_type') === PlanType::TimeBased->value || $get('plan_type') === PlanType::TimeBased),
                        Forms\Components\TextInput::make('session_count')->numeric()->minValue(1)
                            ->visible(fn (Forms\Get $get) => $get('plan_type') === PlanType::SessionPack->value || $get('plan_type') === PlanType::SessionPack),
                        Forms\Components\TextInput::make('validity_days')->numeric()->minValue(1)
                            ->helperText('Days a pack stays usable')
                            ->visible(fn (Forms\Get $get) => $get('plan_type') === PlanType::SessionPack->value || $get('plan_type') === PlanType::SessionPack),
                        Forms\Components\TextInput::make('freeze_allowance_days')->numeric()->default(0),
                        Forms\Components\TextInput::make('guest_passes')->numeric()->default(0),
                        Forms\Components\TextInput::make('min_age')->numeric(),
                        Forms\Components\TextInput::make('max_age')->numeric(),
                    ]),
                ]),
            Forms\Components\Section::make('Pricing')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price (NPR)')
                            ->numeric()
                            ->required()
                            ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => Money::toPaisa($state)),
                        Forms\Components\TextInput::make('admission_fee')
                            ->label('Admission fee (NPR)')
                            ->numeric()
                            ->default(0)
                            ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => Money::toPaisa($state)),
                        Forms\Components\Toggle::make('is_taxable')->default(true)->inline(false),
                        Forms\Components\Toggle::make('price_includes_tax')->default(true)->inline(false),
                    ]),
                ]),
            Forms\Components\Section::make('Availability')
                ->compact()
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Toggle::make('is_off_peak')->live()->inline(false),
                        Forms\Components\TimePicker::make('off_peak_start')->seconds(false)
                            ->visible(fn (Forms\Get $get) => $get('is_off_peak')),
                        Forms\Components\TimePicker::make('off_peak_end')->seconds(false)
                            ->visible(fn (Forms\Get $get) => $get('is_off_peak')),
                        Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                        Forms\Components\DatePicker::make('available_from')->native(false),
                        Forms\Components\DatePicker::make('available_until')->native(false),
                        Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn (MembershipPlan $record) => $record->plan_type === PlanType::SessionPack
                        ? "{$record->session_count} sessions / {$record->validity_days} days"
                        : "{$record->interval_count} {$record->interval_unit?->value}"),
                Tables\Columns\TextColumn::make('plan_type')->badge(),
                Tables\Columns\TextColumn::make('departments.name')->badge()->color('info')->separator(','),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (int $state) => Money::npr($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('admission_fee')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? Money::npr($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('freeze_allowance_days')->label('Freeze days')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subscriptions_count')->counts('subscriptions')->label('Sold'),
                Tables\Columns\IconColumn::make('is_off_peak')->label('Off-peak')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')->options(PlanType::class),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMembershipPlans::route('/'),
        ];
    }
}
