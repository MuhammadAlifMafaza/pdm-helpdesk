<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\ViewEntry;

class TiketPerbaikanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | Informasi Tiket
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Tiket')
                    ->description(
                        'Informasi ini menampilkan detail data dari tiket perbaikan yang diajukan.'
                    )
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([

                        TextEntry::make('kode_tiket')
                            ->label('Nomor Tiket')
                            ->copyable()
                            ->weight('bold'),

                        TextEntry::make('user.name')
                            ->label('Pemohon'),

                        TextEntry::make('ruangan.nama_ruangan')
                            ->label('Ruangan'),

                        TextEntry::make('kepemilikan')
                            ->label('Kepemilikan'),

                        TextEntry::make('keluhan')
                            ->label('Keluhan')
                            ->columnSpanFull(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(
                                fn(?string $state): string => match ($state) {
                                    'Open' => 'info',
                                    'In Progress' => 'warning',
                                    'Close' => 'success',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('status_outcome')
                            ->label('Hasil')
                            ->badge()
                            ->placeholder('-')
                            ->color(
                                fn(?string $state): string => match ($state) {
                                    'Completed' => 'success',
                                    'Rejected' => 'danger',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Deskripsi Kerusakan
                |--------------------------------------------------------------------------
                */

                Section::make('Deskripsi Kerusakan')
                    ->description(
                        'Deskripsi ini menampilkan detail kerusakan yang dilaporkan oleh pemohon.'
                    )
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('deskripsi')
                            ->label('')
                            ->placeholder('Tidak ada deskripsi.')
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Timeline
                |--------------------------------------------------------------------------
                */

                Section::make('Timeline Aktivitas')
                    ->description(
                        'Timeline ini menampilkan semua aktivitas yang terjadi pada tiket perbaikan.'
                    )
                    ->columnSpan(1)
                    ->schema([

                        ViewEntry::make('id')
                            ->hiddenLabel()
                            ->view('filament.pages.tiket.timeline'),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Diskusi
                |--------------------------------------------------------------------------
                */

                Section::make('Diskusi Tiket')
                    ->description(
                        'Komunikasi terkait penanganan tiket'
                    )
                    ->columnSpan(1)
                    ->schema([

                        ViewEntry::make('id')
                            ->hiddenLabel()
                            ->view(
                                'filament.pages.tiket.chat'
                            ),

                    ]),

            ]);
    }
}
