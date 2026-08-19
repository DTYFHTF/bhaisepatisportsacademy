<?php

namespace App\Filament\Resources;

use App\Enums\StaffRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'staff user';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeast(StaffRole::SuperAdmin) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->tel(),
                Forms\Components\Select::make('role')->options(StaffRole::class)->required()->native(false),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (?User $record) => $record === null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->rule('min:8'),
                Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (User $record) => $record->email)
                    ->searchable(['name', 'email']),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('phone')->placeholder('—'),
                Tables\Columns\TextColumn::make('last_login_at')->dateTime('j M Y H:i')->placeholder('Never'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
