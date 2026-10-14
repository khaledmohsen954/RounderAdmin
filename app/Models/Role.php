<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model { protected $fillable=['code']; public function translations(){return $this->hasMany(RoleTranslation::class);} public function users(){return $this->hasMany(User::class);} }
