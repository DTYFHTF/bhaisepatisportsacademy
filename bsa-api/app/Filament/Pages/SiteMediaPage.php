<?php

namespace App\Filament\Pages;

use App\Models\SiteMedia;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteMediaPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Site Media';
    protected static ?string $title           = 'Site Media';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.site-media';

    public ?array $data = [];

    public function mount(): void
    {
        $state = [];

        foreach (SiteMedia::all() as $media) {
            $state[$media->id] = [
                'cloudinary_id' => $media->cloudinary_id,
                'url'           => $media->url,
            ];
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        $groups = SiteMedia::orderBy('sort_order')->get()->groupBy('page_group');

        $schema = [];

        foreach ($groups as $pageGroup => $items) {
            $schema[] = Section::make($pageGroup)
                ->description("Images used on the {$pageGroup} page.")
                ->collapsible()
                ->schema(
                    $items->map(function (SiteMedia $media) {
                        return Section::make($media->label)
                            ->icon('heroicon-o-photo')
                            ->compact()
                            ->schema([
                                FileUpload::make("{$media->id}.cloudinary_id")
                                    ->label('Image')
                                    ->image()
                                    ->disk('cloudinary')
                                    ->directory('bsa/site-media')
                                    ->visibility('public')
                                    ->imagePreviewHeight('200')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(8192)
                                    ->columnSpanFull(),
                                TextInput::make("{$media->id}.url")
                                    ->label('Image URL (auto-filled on upload)')
                                    ->url()
                                    ->readOnly()
                                    ->placeholder('Auto-filled after upload')
                                    ->columnSpanFull(),
                            ]);
                    })->all()
                );
        }

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // Load and save each model instance (not a bulk query update) so the
        // saving() hook fires and derives the Cloudinary url from cloudinary_id.
        foreach (SiteMedia::whereIn('id', array_keys($state))->get() as $media) {
            $media->cloudinary_id = $state[$media->id]['cloudinary_id'] ?? null;
            $media->save();
        }

        // Refresh URLs from the freshly-saved records so the form reflects
        // what actually persisted.
        $this->mount();

        Notification::make()
            ->title('Site media saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Site Media')
                ->submit('save'),
        ];
    }
}
