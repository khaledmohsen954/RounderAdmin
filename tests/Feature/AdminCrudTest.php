<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\Admin;
use App\Modules\Access\Models\PermissionSet;
use App\Modules\Catalog\Models\Service;
use App\Modules\Community\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
/** الحذف مؤكد في الواجهة ومقيد بالخادم؛ تعديل المحتوى لا يفقد الترجمات. */
class AdminCrudTest extends TestCase
{
    use RefreshDatabase;
    /** @return Admin */
    private function admin(): Admin
    {
        $this->seed();
        return Admin::create(['name'=>'Admin','email'=>'crud@example.test','password'=>'StrongPassword123!',
            'locale'=>'ar','is_super_admin'=>true,'is_active'=>true]);
    }
    /** @return void */
    public function test_catalog_pages_are_separate_and_unused_service_can_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/packages')->assertOk()->assertDontSee('id="package-form"',false);
        $this->get('/admin/packages/create')->assertOk()->assertSee('id="package-form"',false);
        $this->get('/admin/services')->assertOk()->assertSee('delete-record-form',false);
        $service=Service::firstOrFail();
        $this->delete('/admin/packages/services/'.$service->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('services',['id'=>$service->id]);
    }
    /** @return void */
    public function test_content_has_edit_and_delete_and_permission_is_enforced(): void
    {
        $this->actingAs($this->admin());
        $payload=['title_ar'=>'إعلان جديد','title_en'=>'New announcement','body_ar'=>'نص الإعلان الجديد',
            'body_en'=>'Announcement content','published'=>0];
        $this->post('/admin/content/announcements',$payload)->assertSessionHasNoErrors();
        $record=Announcement::firstOrFail();
        $this->get('/admin/content/announcements/'.$record->id.'/edit')->assertOk();
        $payload['title_en']='Updated announcement';
        $this->put('/admin/content/announcements/'.$record->id,$payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('announcement_translations',['announcement_id'=>$record->id,'locale'=>'en','title'=>'Updated announcement']);
        $set=PermissionSet::create(['name_ar'=>'عرض','name_en'=>'View','permissions'=>['content.view']]);
        $operator=Admin::create(['name'=>'Read only','email'=>'crud-read@example.test','password'=>'StrongPassword123!',
            'is_active'=>true,'permission_set_id'=>$set->id]);
        $this->actingAs($operator)->delete('/admin/content/announcements/'.$record->id)->assertForbidden();
        $this->assertDatabaseHas('announcements',['id'=>$record->id]);
    }
}
