<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'nota',
        'latitud',
        'longitud',
        'imagen',
        'audio',
        'estado',
        'cliente_id'
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}

