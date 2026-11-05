<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rekapNilaiModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'rekapnilai';
    protected $fillable = [
        'id_Forum',
        'id_User',
        'nilai',
        'nilaiPilihanGanda',
        'nilaiUraian',
        'nilaiMenulis',
    ];

    public function forum(){
        return $this->belongsTo(forumQuizModel::class, 'id_Forum', 'id');
    }

    public function user(){
        return $this->hasMany(user::class, "id", 'id_User');
    }
}
