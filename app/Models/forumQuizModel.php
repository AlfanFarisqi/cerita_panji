<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class forumQuizModel extends Model
{
    use HasFactory;
    protected $table = 'forumquiz';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'judul',
        'idDongeng',
        'sekolah',
        'access_date',
        'expired_date',
        'token',
    ];

    public function dongeng():HasMany
    {
        return $this->hasMany(dongengModel::class, 'id', 'idDongeng');
    }
    
}
