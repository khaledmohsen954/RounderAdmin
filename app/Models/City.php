<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class City extends Model { protected $fillable=['code']; public function translations(){return $this->hasMany(CityTranslation::class);} public function homes(){return $this->hasMany(Home::class);} }
