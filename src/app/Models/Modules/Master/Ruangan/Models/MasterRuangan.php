<?php

namespace App\Models\Modules\Master\Ruangan\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_ruangan', 'nama_gedung'])]
class MasterRuangan extends Model
{
    //
    protected $table = 'master_ruangan';

    protected $fillable = [
        'nama_ruangan',
        'nama_gedung',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
