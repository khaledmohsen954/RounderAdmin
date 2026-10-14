<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceCategory extends Model { protected $fillable=['code','is_active']; public function translations(){return $this->hasMany(ServiceCategoryTranslation::class);} public function services(){return $this->hasMany(Service::class);} }
