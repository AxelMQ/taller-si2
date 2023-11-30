<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    use HasFactory;
    protected $table = 'taller';

    protected $fillable = [
        'direccion',
        'latitud',
        'longitud',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tecnicos(){
        return $this->hasMany(Tecnico::class, 'taller_id');
    }

}
