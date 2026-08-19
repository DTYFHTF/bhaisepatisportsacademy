<?php

namespace App\Filament\Resources;

use App\Enums\BloodGroup;
use App\Enums\CheckInSource;
use App\Enums\Gender;
use App\Enums\GovtIdType;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReferralSource;
use App\Enums\SubscriptionStatus;
use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Department;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Services\BillingService;
use App\Services\CheckInService;
use App\Services\SubscriptionService;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['member_code', 'first_name', 'last_name', 'phone', 'email'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')
                ->icon('heroicon-m-user')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('first_name')->required()->maxLength(80),
                        Forms\Components\TextInput::make('middle_name')->maxLength(80),
                        Forms\Components\TextInput::make('last_name')->required()->maxLength(80),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->maxDate(today())
                            ->native(false),
                        Forms\Components\Select::make('gender')->options(Gender::class)->native(false),
                        Forms\Components\Select::make('blood_group')->options(BloodGroup::class)->native(false),
                        Forms\Components\Select::make('status')
                            ->options(MemberStatus::class)
                            ->default(MemberStatus::Active)
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\DatePicker::make('joined_on')->default(today())->required()->native(false),
                        Forms\Components\FileUpload::make('photo_url')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->directory('members')
                            ->avatar(),
                        Forms\Components\Textarea::make('blacklist_reason')
                            ->visible(fn (Forms\Get $get) => $get('status') === MemberStatus::Blacklisted->value
                                || $get('status') === MemberStatus::Blacklisted)
                            ->columnSpan(3),
                    ]),
                ]),

            Forms\Components\Section::make('Contact & Address')
                ->icon('heroicon-m-map-pin')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->withoutTrashed(),
                            ),
                        Forms\Components\TextInput::make('alt_phone')->tel()->maxLength(20),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('province')->default('Bagmati'),
                        Forms\Components\TextInput::make('district')->default('Lalitpur'),
                        Forms\Components\TextInput::make('municipality')->default('Lalitpur Metropolitan City'),
                        Forms\Components\TextInput::make('ward_no')->numeric()->minValue(1)->maxValue(35),
                        Forms\Components\TextInput::make('tole')->maxLength(120),
                        Forms\Components\Grid::make(2)->columnSpan(1)->schema([
                            Forms\Components\TextInput::make('occupation')->maxLength(80),
                            Forms\Components\TextInput::make('institution')->maxLength(120),
                        ]),
                    ]),
                ]),

            Forms\Components\Section::make('Emergency & Guardian')
                ->icon('heroicon-m-phone-arrow-up-right')
                ->description('Emergency contact for all members; guardian details for minors.')
                ->compact()
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('emergency_contact_name'),
                        Forms\Components\TextInput::make('emergency_contact_relation')->maxLength(40),
                        Forms\Components\TextInput::make('emergency_contact_phone')->tel()->maxLength(20),
                        Forms\Components\TextInput::make('guardian_name'),
                        Forms\Components\TextInput::make('guardian_relation')->maxLength(40),
                        Forms\Components\TextInput::make('guardian_phone')->tel()->maxLength(20),
                    ]),
                ]),

            Forms\Components\Section::make('ID & Medical')
                ->icon('heroicon-m-clipboard-document-check')
                ->compact()
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('govt_id_type')->options(GovtIdType::class)->native(false),
                        Forms\Components\TextInput::make('govt_id_number')->maxLength(60),
                        Forms\Components\Textarea::make('medical_conditions')->rows(2),
                        Forms\Components\Textarea::make('allergies')->rows(2),
                    ]),
                ]),

            Forms\Components\Section::make('Membership Meta')
                ->icon('heroicon-m-megaphone')
                ->compact()
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('referral_source')->options(ReferralSource::class)->native(false),
                        Forms\Components\Select::make('referred_by_member_id')
                            ->label('Referred by')
                            ->relationship('referredBy', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Member $m) => "{$m->full_name} ({$m->member_code})")
                            ->searchable(['first_name', 'last_name', 'member_code', 'phone']),
                        Forms\Components\Toggle::make('marketing_consent')->inline(false),
                        Forms\Components\Textarea::make('notes')->rows(2)->columnSpan(3),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['subscriptions' => fn ($q) => $q
                ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Frozen])
                ->with('plan'),
            ]))
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(28)
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=0d9488&color=fff&size=64'),
                Tables\Columns\TextColumn::make('member_code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Member')
                    ->formatStateUsing(fn (Member $record) => $record->full_name)
                    ->description(fn (Member $record) => $record->phone)
                    ->searchable(['first_name', 'last_name', 'phone'])
                    ->sortable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('subscriptions.plan.name')
                    ->label('Current plan')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('current_ends_on')
                    ->label('Valid until')
                    ->state(fn (Member $record) => $record->subscriptions->max('ends_on'))
                    ->date('j M Y')
                    ->color(fn ($state) => $state === null ? 'gray'
                        : ($state->isPast() ? 'danger' : ($state->diffInDays(today()) >= -7 ? 'warning' : null))),
                Tables\Columns\TextColumn::make('outstanding')
                    ->label('Dues')
                    ->state(fn (Member $record) => $record->outstandingBalance())
                    ->formatStateUsing(fn (int $state) => $state > 0 ? Money::npr($state) : '—')
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray')
                    ->weight(fn (int $state) => $state > 0 ? 'bold' : null),
                Tables\Columns\TextColumn::make('gender')->badge()->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('blood_group')->label('Blood')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tole')
                    ->label('Address')
                    ->description(fn (Member $record) => collect([$record->municipality, $record->ward_no ? "Ward {$record->ward_no}" : null])->filter()->implode(', '))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('occupation')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('referral_source')->badge()->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('joined_on')->date('j M Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(MemberStatus::class),
                Tables\Filters\SelectFilter::make('gender')->options(Gender::class),
                Tables\Filters\SelectFilter::make('referral_source')->options(ReferralSource::class),
                Tables\Filters\Filter::make('minors')
                    ->label('Minors only')
                    ->query(fn (Builder $query) => $query->whereDate('date_of_birth', '>', today()->subYears(18))),
            ])
            ->actions([
                Tables\Actions\Action::make('checkIn')
                    ->label('Check in')
                    ->icon('heroicon-m-arrow-right-end-on-rectangle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(Department::active()->accessControlled()->orderBy('sort_order')->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Member $record, array $data) {
                        $department = Department::findOrFail($data['department_id']);
                        $checkIn = app(CheckInService::class)->checkIn(
                            $record, $department, CheckInSource::FrontDesk, staff: auth()->user(),
                        );

                        if ($checkIn->was_allowed) {
                            Notification::make()->success()
                                ->title("{$record->full_name} checked in to {$department->name}")
                                ->body($checkIn->session_consumed ? 'One session consumed.' : null)
                                ->send();
                        } else {
                            Notification::make()->danger()
                                ->title('Entry denied')
                                ->body($checkIn->denial_reason?->getLabel())
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('newSubscription')
                    ->label('Subscribe')
                    ->icon('heroicon-m-plus-circle')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('membership_plan_id')
                            ->label('Plan')
                            ->options(MembershipPlan::active()->orderBy('sort_order')->get()
                                ->mapWithKeys(fn (MembershipPlan $p) => [$p->id => "{$p->name} — " . Money::npr($p->price)]))
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('discount_id')
                            ->label('Discount')
                            ->options(Discount::active()->pluck('name', 'id'))
                            ->native(false),
                        Forms\Components\DatePicker::make('starts_on')->default(today())->required()->native(false),
                    ])
                    ->action(function (Member $record, array $data) {
                        try {
                            $sub = app(SubscriptionService::class)->subscribe(
                                member: $record,
                                plan: MembershipPlan::findOrFail($data['membership_plan_id']),
                                discount: $data['discount_id'] ? Discount::find($data['discount_id']) : null,
                                startsOn: \Illuminate\Support\Carbon::parse($data['starts_on']),
                                creator: auth()->user(),
                            );
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Could not subscribe')->body(collect($e->errors())->flatten()->first())->send();

                            return;
                        }

                        Notification::make()->success()
                            ->title('Subscribed')
                            ->body("Invoice {$sub->invoice->invoice_number} raised for " . Money::npr($sub->invoice->total) . '.')
                            ->send();
                    }),
                Tables\Actions\Action::make('recordPayment')
                    ->label('Payment')
                    ->icon('heroicon-m-banknotes')
                    ->color('warning')
                    ->visible(fn (Member $record) => $record->outstandingBalance() > 0)
                    ->form(fn (Member $record) => [
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->options($record->invoices()->outstanding()->get()
                                ->mapWithKeys(fn (Invoice $i) => [$i->id => "{$i->invoice_number} — due " . Money::npr($i->balance)]))
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\TextInput::make('amount_rupees')
                            ->label('Amount (NPR)')
                            ->numeric()
                            ->required()
                            ->default(fn (Forms\Get $get) => $get('invoice_id')
                                ? Invoice::find($get('invoice_id'))?->balance / 100
                                : null),
                        Forms\Components\Select::make('method')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Cash)
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\TextInput::make('gateway_txn_id')
                            ->label('Transaction ID')
                            ->visible(fn (Forms\Get $get) => in_array($get('method'), ['esewa', 'khalti', 'bank_transfer', PaymentMethod::Esewa, PaymentMethod::Khalti, PaymentMethod::BankTransfer], true)),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('cheque_number'),
                            Forms\Components\TextInput::make('cheque_bank'),
                            Forms\Components\DatePicker::make('cheque_date')->native(false),
                        ])->visible(fn (Forms\Get $get) => $get('method') === 'cheque' || $get('method') === PaymentMethod::Cheque),
                    ])
                    ->action(function (Member $record, array $data) {
                        try {
                            $payment = app(BillingService::class)->applyPayment(
                                Invoice::findOrFail($data['invoice_id']),
                                [...$data, 'amount' => Money::toPaisa($data['amount_rupees'])],
                                receiver: auth()->user(),
                            );
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Payment rejected')->body(collect($e->errors())->flatten()->first())->send();

                            return;
                        }

                        Notification::make()->success()
                            ->title("Receipt {$payment->receipt_number}")
                            ->body(Money::npr($payment->amount) . ' via ' . $payment->method->getLabel()
                                . ($payment->status->value === 'pending_verification' ? ' — pending verification' : ''))
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
            ])
            ->recordUrl(fn (Member $record) => static::getUrl('view', ['record' => $record]));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubscriptionsRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\CheckInsRelationManager::class,
            RelationManagers\CredentialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view' => Pages\ViewMember::route('/{record}'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
