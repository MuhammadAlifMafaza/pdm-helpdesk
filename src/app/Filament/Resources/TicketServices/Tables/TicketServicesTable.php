<?php

namespace App\Filament\Resources\TicketServices\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // kolom data
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('Keluhan')->label('Nama Layanan')->searchable()->sortable(),
                TextColumn::make('kepemilikan')->label('Kepemilikan')->searchable()->sortable(),
                TextColumn::make('deskripsi')->label('Deskripsi')->searchable()->sortable(),
                TextColumn::make('status')->label('Status')->searchable()->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
        ;
    }
}
