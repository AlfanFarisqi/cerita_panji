<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class artikelModel extends Model
{
    use HasFactory;
    protected $table = 'artikel';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'artikel_link',
        'judul',
        "type",
        'image',
        'deskripsi',
    ];

    public function soal(): HasMany
    {
        return $this->hasMany(soalModel::class, 'id_artikel', 'id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(nilaiModel::class, 'id_artikel', 'id');
    }
}
