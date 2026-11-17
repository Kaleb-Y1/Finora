<?php
namespace Tests\Feature\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SystemAdminTest extends TestCase {
 use RefreshDatabase;
 public function test_admin_pages_are_available(): void {
  $admin=User::factory()->create(['role'=>'admin','status'=>'active']);
  foreach (['admin.users.index','admin.audit.index','admin.logs.activity','admin.logs.error','admin.logs.login','admin.backup.index'] as $route) $this->actingAs($admin)->get(route($route))->assertOk();
 }
 public function test_non_admin_cannot_open_admin_pages(): void {
  $user=User::factory()->create(['role'=>'umum','status'=>'active']);
  $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
 }
 public function test_last_active_admin_cannot_be_disabled(): void {
  $admin=User::factory()->create(['role'=>'admin','status'=>'active']);
  $this->actingAs($admin)->put(route('admin.users.update',$admin),['name'=>$admin->name,'username'=>$admin->username,'email'=>$admin->email,'role'=>'umum','status'=>'inactive'])->assertStatus(422);
 }
}
