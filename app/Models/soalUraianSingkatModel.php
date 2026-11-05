<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class soalUraianSingkatModel extends Model
{
    use HasFactory;
    protected $table = 'soaluraiansingkat';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id',
        'soal',
        'idDongeng',
        'jawaban',
    ];

    public function dongeng():HasMany
    {
        return $this->hasMany(dongengModel::class, "id", "idDongeng");
    }
}
