<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data utama yang digunakan untuk masuk ke sistem.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->helperText(fn ($record): string => $record ? 'Kosongkan jika tidak ingin mengubah password.' : '')
                            ->required(fn ($record): bool => $record === null)
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->maxLength(255),
                    ]),

                Section::make('Akses dan Penempatan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('unit_bidang')
                            ->label('Unit / Bidang')
                            ->required()
                            ->maxLength(255),

                        Select::make('roles')
                            ->label('Role Pengguna')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),

            ]);
    }
}
