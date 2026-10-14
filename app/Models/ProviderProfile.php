<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProviderProfile extends Model { protected $fillable=['user_id','company_name','is_approved']; protected function casts():array{return ['is_approved'=>'boolean'];} public function user(){return $this->belongsTo(User::class);} }
