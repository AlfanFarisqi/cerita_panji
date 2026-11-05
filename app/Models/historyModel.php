<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class historyModel extends Model
{
    use HasFactory;
    protected $table = 'history';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'id_user',
        'id_dongeng',
    ];

    public function dongeng():BelongsTo
    {
        return $this->belongsTo(dongengModel::class, "id_dongeng", "id");
    }
    
}
