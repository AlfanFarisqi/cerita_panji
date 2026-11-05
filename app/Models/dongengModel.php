<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class dongengModel extends Model
{
    use HasFactory;
    protected $table = 'dongeng';
    protected $fillable = [
        'title',
        'filename',
        'pdfURL',
        'view',
        'cover',
    ];

    public function soalPilgan():HasMany
    {
        return $this->hasMany(soalPilganModel::class, "idDongeng", "id");
    }

    public function soalUraianSingkat():hasMany{
        return $this->hasMany(soalUraianSingkatModel::class, "idDongeng", "id");
    }

    public function soalUraianPanjang():HasMany{
        return $this->hasMany(soalUraianPanjangModel::class, "idDongeng");
    }
}
