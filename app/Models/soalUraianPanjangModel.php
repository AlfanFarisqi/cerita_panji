<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class soalUraianPanjangModel extends Model
{
    use HasFactory;
    protected $table = 'soaluraianpanjang';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id',
        'soal',
        'idDongeng',
        'jawaban',
    ];

    public function dongeng(): HasMany
    { 
        return $this->hasMany(dongengModel::class, "id", "idDongeng");
    }
}
