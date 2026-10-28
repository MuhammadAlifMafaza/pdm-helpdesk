<?php

namespace App\Filament\Resources\TicketServices\Pages;

use App\Filament\Resources\TicketServices\TicketServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;

class EditTicketService extends EditRecord
{
    protected static string $resource =
        TicketServiceResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {

        $fields = [
            'keluhan',
            'deskripsi',
            'kepemilikan',
            'ruangan_id',
        ];

        foreach ($fields as $field) {

            if (
                array_key_exists($field, $data)
                &&
                $record->{$field}
                != $data[$field]
            ) {

                $record->updateField(
                    $field,
                    $data[$field]
                );

            }

        }

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
