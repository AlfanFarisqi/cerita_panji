<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rekapSiswaModel extends Model
{
    use HasFactory;
    protected $table = 'history';
    public $timestamps = false;
    protected $fillable = [
        'id_user',
        'id_forum',
        'nilai_total',
    ];
}
