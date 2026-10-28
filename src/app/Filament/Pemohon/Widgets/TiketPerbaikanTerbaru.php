<?php

namespace App\Filament\Pemohon\Widgets;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TiketPerbaikanTerbaru extends TableWidget
{
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Tiket Perbaikan Terbaru';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'span';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TiketPerbaikan::query()
                    ->where('user_id', auth()->id())
                    ->with('ruangan')
                    ->latest('created_at')
            )
            ->columns([

                Tables\Columns\TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->limit(45)
                    ->wrap(),

                Tables\Columns\TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Open' => 'warning',
                        'In Progress' => 'info',
                        'Close' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5]);
    }
}
