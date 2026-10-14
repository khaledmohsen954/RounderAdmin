<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\Role; use App\Modules\Identity\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class ResidentAuthenticationTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void { parent::setUp(); $this->seed(); }
 public function test_resident_registration_hashes_password_and_issues_token():void {
  $response=$this->postJson('/api/v1/auth/register',[
   'name'=>'Test Resident','email'=>'resident@example.test','password'=>'RounderPass123','password_confirmation'=>'RounderPass123','locale'=>'ar'
  ]);
  $response->assertCreated()->assertJsonPath('data.user.email','resident@example.test')->assertJsonStructure(['data'=>['user'=>['id','name','email','locale'],'token']]);
  $user=User::where('email','resident@example.test')->firstOrFail();
  $this->assertNotSame('RounderPass123',$user->password);
  $this->assertSame('resident',$user->role->code);
 }
 public function test_registration_rejects_weak_password_and_duplicate_email():void {
  $this->postJson('/api/v1/auth/register',['name'=>'Test Resident','email'=>'bad@example.test','password'=>'weak','password_confirmation'=>'weak'])->assertUnprocessable();
  $residentRole=Role::where('code','resident')->firstOrFail();
  User::create(['name'=>'Existing','email'=>'existing@example.test','password'=>'RounderPass123','role_id'=>$residentRole->id,'locale'=>'en']);
  $this->postJson('/api/v1/auth/register',['name'=>'Another','email'=>'existing@example.test','password'=>'RounderPass123','password_confirmation'=>'RounderPass123'])->assertUnprocessable();
 }
}
