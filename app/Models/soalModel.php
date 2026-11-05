<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class soalModel extends Model
{
    use HasFactory;
    protected $table = 'soal';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'id_artikel',
        'soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'jawaban',
        'id_artikel',
        'score',
    ];
    public function artikel()
    {
        return $this->belongsTo(ArtikelModel::class, 'id_artikel');
    }

}
