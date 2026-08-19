<?php

namespace App\Filament\Pages;

use App\Enums\StaffRole;
use App\Models\Setting;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Organisation settings';

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAtLeast(StaffRole::Manager) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'org_name' => Setting::get('org_name', 'Bhaisepati Sports Academy'),
            'org_address' => Setting::get('org_address'),
            'org_phone' => Setting::get('org_phone'),
            'vat_number' => Setting::get('vat_number'),
            'pan_number' => Setting::get('pan_number'),
            'tax_rate_percent' => Setting::get('tax_rate_percent', 13),
            'current_fiscal_year' => Setting::get('current_fiscal_year', '2082-83'),
            'fiscal_year_started_on' => Setting::get('fiscal_year_started_on', '2026-07-16'),
            'dues_grace_days' => Setting::get('dues_grace_days', 7),
            'dues_block_threshold_rupees' => ((int) Setting::get('dues_block_threshold', 0)) / 100,
            'receipt_footer' => Setting::get('receipt_footer'),
            'member_code_prefix' => Setting::get('member_code_prefix', 'BSA'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Organisation')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('org_name')->label('Name')->required(),
                        Forms\Components\TextInput::make('org_phone')->label('Phone'),
                        Forms\Components\TextInput::make('org_address')->label('Address'),
                        Forms\Components\TextInput::make('vat_number')->label('VAT no.'),
                        Forms\Components\TextInput::make('pan_number')->label('PAN no.'),
                        Forms\Components\TextInput::make('member_code_prefix')->maxLength(5),
                    ]),
                ]),
            Forms\Components\Section::make('Tax & fiscal year')
                ->description('Roll the fiscal year each Shrawan — invoice and receipt sequences restart per year.')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('tax_rate_percent')->label('VAT %')->numeric()->required(),
                        Forms\Components\TextInput::make('current_fiscal_year')
                            ->label('Fiscal year label')
                            ->placeholder('2082-83')
                            ->required(),
                        Forms\Components\DatePicker::make('fiscal_year_started_on')->native(false)->required(),
                    ]),
                ]),
            Forms\Components\Section::make('Dues policy')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('dues_grace_days')
                            ->label('Grace days after due date')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('dues_block_threshold_rupees')
                            ->label('Block entry when dues exceed (NPR)')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('receipt_footer')->rows(2),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('org_name', $data['org_name'], 'org');
        Setting::set('org_address', $data['org_address'], 'org');
        Setting::set('org_phone', $data['org_phone'], 'org');
        Setting::set('vat_number', $data['vat_number'], 'org');
        Setting::set('pan_number', $data['pan_number'], 'org');
        Setting::set('member_code_prefix', $data['member_code_prefix'], 'org');
        Setting::set('tax_rate_percent', $data['tax_rate_percent'], 'billing');
        Setting::set('current_fiscal_year', $data['current_fiscal_year'], 'billing');
        Setting::set('fiscal_year_started_on', $data['fiscal_year_started_on'], 'billing');
        Setting::set('dues_grace_days', $data['dues_grace_days'], 'billing');
        Setting::set('dues_block_threshold', Money::toPaisa($data['dues_block_threshold_rupees']), 'billing');
        Setting::set('receipt_footer', $data['receipt_footer'], 'billing');

        Notification::make()->success()->title('Settings saved')->send();
    }
}
