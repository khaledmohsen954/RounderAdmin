<?php
namespace Tests\Feature;

use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_administrative_pages_render_in_both_languages(): void
    {
        $this->seed();
        $admin=\App\Modules\Identity\Models\Admin::create(['is_super_admin'=>true,'is_active'=>true,'name'=>'Administrator','email'=>'pages@test.example',
            'password'=>'StrongPassword123!', 'locale'=>'ar',
            'role_id'=>DB::table('roles')->where('code','admin')->value('id')]);
        $this->actingAs($admin);
        foreach(['ar','en'] as $locale) {
            foreach(['dashboard','about','users','realestate','providers','packages','content','emergencies','service-orders'] as $page) {
                $this->withSession(['locale'=>$locale])->get('/admin/'.$page)->assertOk();
            }
        }
    }
}
