<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Service extends Model { protected $fillable=['service_category_id','code','base_price','is_active']; protected function casts():array{return ['base_price'=>'decimal:2','is_active'=>'boolean'];} public function category(){return $this->belongsTo(ServiceCategory::class,'service_category_id');} public function translations(){return $this->hasMany(ServiceTranslation::class);} public function bookings(){return $this->hasMany(Booking::class);} }
