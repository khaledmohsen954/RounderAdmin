<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class Home extends Model { protected $fillable=['name','address','city_id']; public function city(){return $this->belongsTo(City::class);} public function residents(){return $this->belongsToMany(User::class,'resident_homes')->withPivot(['unit_label','is_primary'])->withTimestamps();} public function bookings(){return $this->hasMany(Booking::class);} }
