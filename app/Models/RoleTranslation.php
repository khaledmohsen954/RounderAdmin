<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RoleTranslation extends Model { protected $fillable=['role_id','locale','name']; public function role(){return $this->belongsTo(Role::class);} }
