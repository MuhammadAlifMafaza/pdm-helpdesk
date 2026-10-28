<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Section::make('Informasi Pengguna')
                    ->description('Detail akun dan akses pengguna dalam sistem.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Pengguna')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable()
                            ->copyMessage('Email berhasil disalin'),

                        TextEntry::make('unit_bidang')
                            ->label('Unit / Bidang')
                            ->placeholder('-'),

                        TextEntry::make('roles.name')
                            ->label('Role Pengguna')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('-'),
                    ]),

                Section::make('Aktivitas Akun')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Terdaftar')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),
                    ]),
            ]);
    }
}
