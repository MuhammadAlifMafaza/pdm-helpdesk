<?php

namespace App\Filament\Resources\Master\MasterBarangs\Schemas;

use App\Models\Modules\Master\Barang\Models\MasterBarang;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class MasterBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Barang')
                    ->description(
                        'Kelola data barang yang dapat digunakan dalam pengajuan barang.'
                    )
                    ->schema([
                        Select::make('kategori_barang_id')
                            ->label('Kategori Barang')
                            ->relationship(
                                name: 'kategoriBarang',
                                titleAttribute: 'nama_kategori',
                                modifyQueryUsing: fn($query) => $query
                                    ->where('is_active', true)
                                    ->whereNull('deleted_at')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText(
                                'Hanya kategori barang yang aktif yang dapat dipilih.'
                            ),

                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->rules(function (?MasterBarang $record, callable $get) {
                                return [
                                    Rule::unique('master_barang', 'nama_barang')
                                        ->where(function ($query) use ($get) {
                                            $query
                                                ->where(
                                                    'kategori_barang_id',
                                                    $get('kategori_barang_id')
                                                )
                                                ->whereNull('deleted_at');
                                        })
                                        ->ignore($record?->id),
                                ];
                            })
                            ->validationMessages([
                                'unique' => 'Nama barang pada kategori tersebut sudah digunakan.',
                            ])
                            ->helperText(
                                'Nama barang harus unik dalam kategori yang dipilih.'
                            ),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder(
                                'Masukkan spesifikasi atau keterangan standar barang.'
                            )
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Status Barang')
                            ->live()
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->default(true)
                            ->inline(false)
                            ->helperText(
                                fn($state): string =>
                                    $state
                                    ? 'Aktif — barang dapat digunakan pada data baru.'
                                    : 'Tidak Aktif — barang tidak dapat digunakan pada data baru.'
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}
