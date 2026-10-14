<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class Booking extends Model {
 protected $fillable=['resident_id','home_id','service_id','provider_id','booking_status_id','scheduled_at','total_amount','notes'];
 protected function casts():array{return ['scheduled_at'=>'datetime','total_amount'=>'decimal:2'];}
 public function resident(){return $this->belongsTo(User::class,'resident_id');} public function home(){return $this->belongsTo(Home::class);} public function service(){return $this->belongsTo(Service::class);} public function provider(){return $this->belongsTo(User::class,'provider_id');} public function status(){return $this->belongsTo(BookingStatus::class,'booking_status_id');} public function payments(){return $this->hasMany(Payment::class);}
}
