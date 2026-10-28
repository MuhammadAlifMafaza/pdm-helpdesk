<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AdminTiketTerbaru extends TableWidget
{
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Tiket Perbaikan Terbaru';
    protected int|string|array $columnSpan = 'span';
    protected static ?int $sort = 5;

    protected function getTablePollingInterval(): ?string
    {
        return '30s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TiketPerbaikan::query()
                    ->with([
                        'user',
                        'ruangan',
                    ])
                    ->latest('created_at')
            )
            ->columns([

                Tables\Columns\TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->limit(45)
                    ->tooltip(
                        fn(TiketPerbaikan $record) => $record->keluhan
                    ),

                Tables\Columns\TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable(),

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
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25]);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'admin_super',
            'super_admin',
        ]) ?? false;
    }

}
