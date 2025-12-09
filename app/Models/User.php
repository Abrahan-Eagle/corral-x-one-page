<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\UserTrait;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, UserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'light',
        'google_id',        // ID único proporcionado por Google
        'given_name',       // Nombre de pila
        'family_name',      // Apellido
        'profile_pic',      // URL de la imagen de perfil de Google
        'AccessToken',
        'role',  // Rol del usuario (admin, cliente, etc.
        'completed_onboarding'
    ];

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'AccessToken',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'int',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // 'completed_onboarding' => 'number',
    ];

    /**
     * Relación 1:1 con Profile
     * Un usuario tiene un perfil de marketplace
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si el usuario completó el onboarding
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->completed_onboarding;
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }
}