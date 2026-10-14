<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class Payment extends Model { protected $fillable=['booking_id','payment_status_id','amount','reference']; protected function casts():array{return ['amount'=>'decimal:2'];} public function booking(){return $this->belongsTo(Booking::class);} public function status(){return $this->belongsTo(PaymentStatus::class,'payment_status_id');} }
