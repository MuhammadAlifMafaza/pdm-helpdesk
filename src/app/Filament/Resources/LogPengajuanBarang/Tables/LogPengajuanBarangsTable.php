<?php

namespace App\Filament\Resources\LogPengajuanBarang\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogPengajuanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('pengajuan.kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Dilakukan Oleh')
                    ->placeholder('-'),

                TextColumn::make('event_name')
                    ->label('Aktivitas')
                    ->badge(),

                TextColumn::make('event_description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->limit(80),
            ])
            ->filters([
                //
            ]);
    }
}
