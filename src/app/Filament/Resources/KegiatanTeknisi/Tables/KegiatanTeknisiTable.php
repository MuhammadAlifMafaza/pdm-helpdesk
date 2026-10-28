<?php

namespace App\Filament\Resources\KegiatanTeknisi\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
// Filament Action Imports
use Filament\Tables\Columns\TextColumn;
// Filament Tables Import
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
// Filament Forms Import
use Illuminate\Database\Eloquent\Builder;

class KegiatanTeknisiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('teknisi.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),

                TextColumn::make('petugas.name')
                    ->label('Nama Petugas')
                    ->placeholder('-')
                    ->separator(', ')
                    ->wrap(),

                TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->timezone('Asia/Jakarta'),

                TextColumn::make('deskripsi_kegiatan')
                    ->label('Catatan Kegiatan')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->deskripsi_kegiatan)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->since()
                    ->timezone('Asia/Jakarta'),
            ])

            ->actions([

                ViewAction::make(),

                EditAction::make()
                    ->visible(function ($record) {

                        $user = auth()->user();

                        if (
                            $user->hasRole('super_admin') ||
                            $user->hasRole('admin')
                        ) {
                            return true;
                        }

                        return
                            $user->hasRole('teknisi')
                            &&
                            $record->teknisi_id == $user->id;
                    })
                    ->disabled(fn ($record) => $record->trashed()),

                DeleteAction::make()
                    ->visible(function ($record) {

                        $user = auth()->user();

                        if (
                            $user->hasRole('super_admin') ||
                            $user->hasRole('admin')
                        ) {
                            return true;
                        }

                        return
                            $user->hasRole('teknisi')
                            &&
                            $record->teknisi_id == $user->id;
                    }),

                RestoreAction::make()
                    ->visible(fn (): bool => auth()->user()->hasAnyRole(['super_admin', 'admin'])),

            ])

            ->filters([

                SelectFilter::make('teknisi_id')
                    ->label('Teknisi')
                    ->relationship(
                        'teknisi',
                        'name',
                        fn (Builder $query) => $query->role(['teknisi', 'admin', 'super_admin'])

                    )
                    ->searchable()
                    ->preload(),

                Filter::make('tanggal')
                    ->label('Tanggal Kegiatan')
                    ->form([
                        DatePicker::make('tanggal_mulai')
                            ->label('Dari'),

                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when(
                                $data['tanggal_mulai'],
                                fn (Builder $query, $date) => $query->whereDate('tanggal', '>=', $date)
                            )
                            ->when(
                                $data['tanggal_selesai'],
                                fn (Builder $query, $date) => $query->whereDate('tanggal', '<=', $date)
                            );

                    }),

                SelectFilter::make('bulan')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->query(function (Builder $query, array $data) {

                        if (blank($data['value'])) {
                            return $query;
                        }

                        return $query->whereMonth(
                            'tanggal',
                            $data['value']
                        );

                    }),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        collect(range(
                            now()->year,
                            now()->year - 5
                        ))
                            ->mapWithKeys(fn ($year) => [
                                $year => $year,
                            ])
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {

                        if (blank($data['value'])) {
                            return $query;
                        }

                        return $query->whereYear(
                            'tanggal',
                            $data['value']
                        );

                    }),

                TrashedFilter::make(),

            ]);
    }
}
