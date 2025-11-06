<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'balance',
        'image'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Automatically hash passwords when setting them
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    } 

    public function leads(){
        return $this->hasMany(Lead::class);
    } 

    public function properties(){
        return $this->hasMany(Property::class);
    }
}