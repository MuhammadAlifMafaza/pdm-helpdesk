<?php

namespace App\Filament\Resources\PengajuanBarangs\Schemas;

use App\Models\Modules\Master\Barang\Models\MasterBarang;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PengajuanBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | Kode Pengajuan
                |--------------------------------------------------------------------------
                */

                TextInput::make('kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn(['edit', 'view']),

                /*
                |--------------------------------------------------------------------------
                | Pemohon
                |--------------------------------------------------------------------------
                */

                TextInput::make('pemohon')
                    ->label('Pemohon')
                    ->default(
                        fn(): ?string => auth()->user()?->name
                    )
                    ->afterStateHydrated(
                        function (Set $set, ?PengajuanBarang $record): void {
                            if (!$record?->user_id) {
                                return;
                            }

                            $set(
                                'pemohon',
                                $record->user?->name
                            );
                        }
                    )
                    ->disabled()
                    ->dehydrated(false),

                /*
                |--------------------------------------------------------------------------
                | Kategori Barang
                |--------------------------------------------------------------------------
                */

                Select::make('kategori_barang_id')
                    ->label('Kategori Barang')
                    ->options(
                        function (?PengajuanBarang $record): array {
                            $query = MasterKategoriBarang::query()
                                ->orderBy('nama_kategori');

                            /*
                             * Pengajuan baru:
                             * hanya kategori aktif.
                             *
                             * Pengajuan lama:
                             * kategori yang sedang digunakan tetap
                             * dapat ditampilkan agar histori tidak rusak
                             * ketika kategori menjadi nonaktif.
                             */
                            if (
                                $record?->barang?->kategori_barang_id
                            ) {
                                $currentKategoriId =
                                    $record->barang->kategori_barang_id;

                                $query->where(
                                    function ($query) use ($currentKategoriId): void {
                                        $query
                                            ->where(
                                                function ($query): void {
                                                    $query
                                                        ->where(
                                                            'is_active',
                                                            true
                                                        )
                                                        ->whereNull(
                                                            'deleted_at'
                                                        );
                                                }
                                            )
                                            ->orWhere(
                                                $query->getModel()
                                                    ->getQualifiedKeyName(),
                                                $currentKategoriId
                                            );
                                    }
                                );
                            } else {
                                $query
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->whereNull(
                                        'deleted_at'
                                    );
                            }

                            return $query
                                ->pluck(
                                    'nama_kategori',
                                    'id'
                                )
                                ->toArray();
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->required(
                        fn(
                        ?PengajuanBarang $record
                    ): bool => $record === null
                            || filled($record->barang_id)
                    )
                    ->dehydrated(false)
                    ->afterStateHydrated(
                        function (Set $set, ?PengajuanBarang $record): void {
                            if (!$record?->barang_id) {
                                return;
                            }

                            $set(
                                'kategori_barang_id',
                                $record->barang?->kategori_barang_id
                            );
                        }
                    )
                    ->afterStateUpdated(
                        function (Set $set): void {
                            /*
                             * Kategori berubah:
                             * barang sebelumnya harus di-reset.
                             */
                            $set(
                                'barang_id',
                                null
                            );

                            /*
                             * Spesifikasi berkaitan dengan barang
                             * yang dipilih, sehingga harus diisi
                             * kembali setelah barang baru dipilih.
                             */
                            $set(
                                'spesifikasi_barang',
                                null
                            );
                        }
                    )
                    ->helperText(
                        'Pilih kategori barang yang sesuai.'
                    ),

                /*
                |--------------------------------------------------------------------------
                | Master Barang
                |--------------------------------------------------------------------------
                */

                Select::make('barang_id')
                    ->label('Nama Barang')
                    ->options(
                        function (Get $get, ?PengajuanBarang $record): array {
                            $kategoriId =
                                $get('kategori_barang_id');

                            if (!$kategoriId) {
                                return [];
                            }

                            $currentBarangId =
                                $record?->barang_id
                                ?: $get('barang_id');

                            return MasterBarang::query()
                                ->where(
                                    'kategori_barang_id',
                                    $kategoriId
                                )
                                ->where(
                                    function ($query) use ($currentBarangId): void {
                                        /*
                                         * Barang yang dapat dipilih
                                         * untuk transaksi baru.
                                         */
                                        $query->where(
                                            function ($query): void {
                                                $query
                                                    ->where(
                                                        'is_active',
                                                        true
                                                    )
                                                    ->whereNull(
                                                        'deleted_at'
                                                    )
                                                    ->whereHas(
                                                        'kategoriBarang',
                                                        function ($query): void {
                                                            $query
                                                                ->where(
                                                                    'is_active',
                                                                    true
                                                                )
                                                                ->whereNull(
                                                                    'deleted_at'
                                                                );
                                                        }
                                                    );
                                            }
                                        );

                                        /*
                                         * Saat edit transaksi lama,
                                         * barang yang sedang digunakan
                                         * tetap ditampilkan.
                                         */
                                        if ($currentBarangId) {
                                            $query->orWhere(
                                                $query->getModel()
                                                    ->getQualifiedKeyName(),
                                                $currentBarangId
                                            );
                                        }
                                    }
                                )
                                ->orderBy('nama_barang')
                                ->pluck(
                                    'nama_barang',
                                    'id'
                                )
                                ->toArray();
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->required(
                        fn(
                        ?PengajuanBarang $record
                    ): bool => $record === null
                            || filled($record->barang_id)
                    )
                    ->disabled(
                        fn(Get $get): bool => blank(
                            $get('kategori_barang_id')
                        )
                    )
                    ->afterStateHydrated(
                        function (Set $set, ?PengajuanBarang $record): void {
                            if (!$record) {
                                return;
                            }

                            $set(
                                'barang_id',
                                $record->barang_id
                            );

                            /*
                             * Spesifikasi adalah data transaksi.
                             *
                             * Hanya mengambil nilai yang sudah
                             * tersimpan pada pengajuan.
                             */
                            $set(
                                'spesifikasi_barang',
                                $record->spesifikasi_barang
                            );
                        }
                    )
                    ->helperText(
                        'Nama barang dikelola melalui Master Barang.'
                    ),

                /*
                |--------------------------------------------------------------------------
                | Jumlah
                |--------------------------------------------------------------------------
                */

                TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                /*
                |--------------------------------------------------------------------------
                | Spesifikasi Barang
                |--------------------------------------------------------------------------
                */

                Textarea::make('spesifikasi_barang')
                    ->label('Spesifikasi Barang')
                    ->placeholder(
                        'Contoh: tipe/model, ukuran atau kapasitas, spesifikasi teknis, dan kompatibilitas yang dibutuhkan.'
                    )
                    ->helperText(
                        'Opsional. Isi spesifikasi atau kebutuhan teknis khusus sesuai barang yang dipilih.'
                    )
                    ->rows(4)
                    ->maxLength(2000),

                /*
                |--------------------------------------------------------------------------
                | Alasan
                |--------------------------------------------------------------------------
                */

                Textarea::make('alasan')
                    ->label('Alasan Pengajuan')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->placeholder(
                        'Jelaskan alasan pengajuan barang'
                    )
                    ->columnSpanFull(),
            ]);
    }
}
