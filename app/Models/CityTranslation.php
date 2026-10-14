<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CityTranslation extends Model { protected $fillable=['city_id','locale','name']; public function city(){return $this->belongsTo(City::class);} }
