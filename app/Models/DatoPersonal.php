<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatoPersonal extends Model
{
    use HasFactory;

    protected $table = 'datosPersonales';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
