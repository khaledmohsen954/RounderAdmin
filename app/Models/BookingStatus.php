<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class BookingStatus extends Model { protected $fillable=['code']; public function translations(){return $this->hasMany(BookingStatusTranslation::class);} public function bookings(){return $this->hasMany(Booking::class);} }
