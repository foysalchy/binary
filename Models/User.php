<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

 // Creating a relationship quizzes
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Creating a relationship questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Creating a relationship chirps
    public function chirps()
    {
        return $this->hasMany(Chirp::class);
    }

    // Creating a relationship prattles
    public function prattles()
    {
        return $this->hasMany(Prattle::class);
    }

    // Creating a relationship playlists
    public function playlists()
    {
        return $this->hasMany(playlist::class);
    }
    
}
