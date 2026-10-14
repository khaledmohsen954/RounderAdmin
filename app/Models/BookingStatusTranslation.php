<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class BookingStatusTranslation extends Model { protected $fillable=['booking_status_id','locale','name']; public function status(){return $this->belongsTo(BookingStatus::class,'booking_status_id');} }
