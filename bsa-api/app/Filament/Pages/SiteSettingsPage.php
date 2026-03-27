<?php

namespace App\Filament\Pages;

use App\Models\SiteSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title           = 'Site Settings';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSettings::current()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Brand')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Store Name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('store_tagline')
                            ->label('Tagline')
                            ->maxLength(200)
                            ->columnSpanFull(),
                    ]),

                Section::make('Logos')
                    ->description('Upload updated logo or icon. Changes take effect immediately - no redeployment needed.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Header & Footer Logo')
                            ->helperText('Recommended: PNG with transparent background, ~400×100px. Shown in header and footer.')
                            ->image()
                            ->imagePreviewHeight('60')
                            ->disk('public')
                            ->directory('logos')
                            ->visibility('public')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp']),

                        FileUpload::make('icon_path')
                            ->label('Icon / Favicon Variant')
                            ->helperText('Recommended: PNG square ~100×100px. Shown in footer bottom bar.')
                            ->image()
                            ->imagePreviewHeight('60')
                            ->disk('public')
                            ->directory('logos')
                            ->visibility('public')
                            ->maxSize(256)
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp']),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('contact_phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('contact_address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Store Location')
                    ->description('Used to calculate delivery routes on the tracking page.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.0000001),
                        TextInput::make('store_lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.0000001),
                    ]),

                Section::make('Social Links')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://instagram.com/bsa.example.com'),
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/bsa.example.com'),
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Number')
                            ->tel()
                            ->placeholder('9821357118')
                            ->helperText('10-digit Nepal number. Used to open a direct WhatsApp chat.'),
                    ]),

                Section::make('Content Snippets')
                    ->columns(1)
                    ->schema([
                        TextInput::make('delivery_tagline')
                            ->label('Delivery Info Line')
                            ->maxLength(200),
                        TextInput::make('return_tagline')
                            ->label('Return Info Line')
                            ->maxLength(200),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = SiteSettings::current();
        $settings->update($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }
}

