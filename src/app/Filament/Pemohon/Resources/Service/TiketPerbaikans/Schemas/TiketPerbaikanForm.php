<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TiketPerbaikanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('ruangan_id')
                    ->label('Lokasi')
                    ->relationship(
                        name: 'ruangan',
                        titleAttribute: 'nama_ruangan'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('kepemilikan')
                    ->label('Kepemilikan')
                    ->options([
                        'Inventaris Kantor' => 'Inventaris Kantor',
                        'Pribadi' => 'Pribadi',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required(),

                TextInput::make('keluhan')
                    ->label('Keluhan')
                    ->required()
                    ->maxLength(255),

                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(5)
                    ->required(),
            ]);
    }
}
