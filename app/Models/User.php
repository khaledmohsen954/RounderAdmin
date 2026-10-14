<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Foundation\\Auth\\User as Authenticatable;
use Laravel\\Sanctum\\HasApiTokens;
use Illuminate\\Notifications\\Notifiable;
class User extends Authenticatable {
 use HasApiTokens, HasFactory, Notifiable;
 protected $fillable=['name','email','phone','password','role_id','locale'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
 public function role(){return $this->belongsTo(Role::class);}
 public function residentHomes(){return $this->belongsToMany(Home::class,'resident_homes')->withPivot(['unit_label','is_primary'])->withTimestamps();}
 public function bookings(){return $this->hasMany(Booking::class,'resident_id');}
}
