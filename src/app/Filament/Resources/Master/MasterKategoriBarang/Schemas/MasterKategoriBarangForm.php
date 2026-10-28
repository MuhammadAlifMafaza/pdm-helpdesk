<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterKategoriBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description(
                        'Masukkan informasi kategori barang yang akan digunakan dalam Master Barang.'
                    )
                    ->schema([
                        TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: 'master_kategori_barang',
                                column: 'nama_kategori',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule) {
                                    return $rule->whereNull('deleted_at');
                                },
                            )
                            ->validationMessages([
                                'unique' => 'Nama kategori sudah digunakan.',
                            ])
                            ->placeholder('Contoh: Sparepart'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder(
                                'Masukkan deskripsi atau keterangan kategori.'
                            )
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Status Kategori')
                            ->live()
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->default(true)
                            ->helperText(
                                fn ($state) => $state
                                    ? 'Aktif — kategori dapat digunakan.'
                                    : 'Tidak Aktif — kategori tidak dapat digunakan.'
                            ),

                    ])
                    ->columns(2),
            ]);
    }
}
