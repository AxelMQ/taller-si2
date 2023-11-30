<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'usuario',
        'password',
        'tipo',
    ];

    protected $hidden = [
        'password',
    ];

    public function cliente(){
        return $this->hasOne(Cliente::class, 'user_id');
    }

    public function tecnico(){
        return $this->hasOne(Tecnico::class, 'user_id');
    }

    public function taller(){
        return $this->hasOne(Taller::class, 'user_id');
    }

    public function datoPersonal(){
        return $this->hasOne(DatoPersonal::class, 'user_id');
    }

}
