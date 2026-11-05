<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class soalPilganModel extends Model
{
    use HasFactory;
    protected $table = 'soalpilgan';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'soal',
        'idDongeng',
        'opsi_1',
        'opsi_2',
        'opsi_3',
        'opsi_4',
        'jawaban',
    ];

    public function dongeng()
    { 
        return $this->belongsTo(dongengModel::class, "idDongeng", "id");
    }
    
}
