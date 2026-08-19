<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\AccessCredential;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'credentials';

    protected static ?string $title = 'Access credentials';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('identifier_hint')
                    ->label('Identifier')
                    ->formatStateUsing(fn (?string $state) => $state ? "···{$state}" : '—'),
                Tables\Columns\TextColumn::make('label')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Deposit')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? Money::npr($state) : '—'),
                Tables\Columns\TextColumn::make('issued_at')->dateTime('j M Y'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('issue')
                    ->label('Issue credential')
                    ->icon('heroicon-m-key')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options(CredentialType::class)
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('identifier')
                            ->label('Card / code identifier')
                            ->helperText('Scan or type the raw identifier. Only a hash is stored.')
                            ->required(),
                        Forms\Components\TextInput::make('label')->placeholder('e.g. Blue keyfob'),
                        Forms\Components\TextInput::make('deposit_rupees')->label('Deposit (NPR)')->numeric()->default(0),
                    ])
                    ->action(function (array $data) {
                        $raw = trim($data['identifier']);

                        $this->getOwnerRecord()->credentials()->create([
                            'type' => $data['type'],
                            'identifier_hash' => AccessCredential::hashIdentifier($raw),
                            'identifier_hint' => substr($raw, -4),
                            'label' => $data['label'] ?? null,
                            'deposit_amount' => Money::toPaisa($data['deposit_rupees'] ?? 0),
                            'status' => CredentialStatus::Active,
                            'issued_at' => now(),
                            'issued_by' => auth()->id(),
                        ]);

                        Notification::make()->success()->title('Credential issued')->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->visible(fn (AccessCredential $record) => $record->status === CredentialStatus::Active)
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                CredentialStatus::Revoked->value => 'Revoked',
                                CredentialStatus::Lost->value => 'Lost',
                            ])
                            ->default(CredentialStatus::Revoked->value)
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('revoke_reason'),
                    ])
                    ->action(function (AccessCredential $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'revoked_at' => now(),
                            'revoke_reason' => $data['revoke_reason'] ?? null,
                        ]);

                        Notification::make()->success()->title('Credential revoked')->send();
                    }),
            ]);
    }
}
