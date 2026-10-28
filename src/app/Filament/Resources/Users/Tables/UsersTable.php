<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->striped()
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('unit_bidang')
                    ->label('Unit / Bidang')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->separator(', '),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('roles')
                    ->label('Peran Pengguna')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Pengguna')
                    ->modalDescription('Masukkan password baru untuk pengguna ini.')
                    ->modalSubmitActionLabel('Simpan Password Baru')
                    ->form([
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->same('password_confirmation'),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->revealable()
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => $data['password'],
                        ]);
                    })
                    ->successNotificationTitle('Password pengguna berhasil direset'),
            ])
        ;
    }
}
