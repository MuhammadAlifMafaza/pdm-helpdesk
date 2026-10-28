<?php

namespace App\Filament\Widgets\Teknisi;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TeknisiTiketAktif extends TableWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Pekerjaan yang Sedang Ditangani';

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }

    public function table(Table $table): Table
    {
        $userId = auth()->id();

        return $table
            ->query(
                TiketPerbaikan::query()
                    ->currentlyHandledByTechnician($userId)
                    ->where('status', 'In Progress')
                    ->with([
                        'user',
                        'ruangan',
                    ])
                    ->latest('updated_at')
            )

            ->columns([

                Tables\Columns\TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->limit(45)
                    ->wrap(),

                Tables\Columns\TextColumn::make('ruangan.nama')
                    ->label('Ruangan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(
                        fn ($state) => match ($state) {
                            'In Progress' => 'Sedang Dikerjakan',
                            default => $state,
                        }
                    ),
            ])

            ->defaultSort(
                'updated_at',
                'desc'
            )

            ->paginated([
                5,
                10,
                25,
            ]);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('teknisi') ?? false;
    }
}
