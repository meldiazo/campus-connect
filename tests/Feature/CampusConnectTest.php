<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Resource;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampusConnectTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'estudiante'): User
    {
        return User::factory()->create(['role' => $role, 'password' => Hash::make('password')]);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'tecnologia'], ['name' => 'Tecnología']);
    }

    private function request(User $student, ?Category $category = null): ServiceRequest
    {
        return ServiceRequest::create(['user_id' => $student->id, 'category_id' => ($category ?? $this->category())->id, 'title' => 'Equipo sin señal', 'description' => 'Revisar equipo del aula.', 'location' => 'Edificio A', 'priority' => 'Media', 'status' => 'Pendiente']);
    }

    public function test_login_and_logout_work(): void
    {
        $user = $this->user();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/requests')->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_student_can_create_request_and_history_is_recorded(): void
    {
        $student = $this->user();
        $category = $this->category();
        $this->actingAs($student)->get(route('requests.create'))->assertOk()->assertDontSee('name="priority"');
        $this->actingAs($student)->post('/requests', ['title' => 'Luz dañada', 'description' => 'La luz parpadea.', 'category_id' => $category->id, 'location' => 'Biblioteca', 'priority' => 'Urgente'])->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['title' => 'Luz dañada', 'user_id' => $student->id, 'status' => 'Pendiente', 'priority' => 'Media']);
        $this->assertDatabaseHas('request_histories', ['action' => 'Creación']);
    }

    public function test_student_can_attach_evidence(): void
    {
        Storage::fake('public');
        $student = $this->user();
        $request = $this->request($student);
        $this->actingAs($student)->post(route('requests.attachments', $request), ['evidence' => UploadedFile::fake()->image('evidence.jpg')])->assertRedirect();
        $this->assertDatabaseHas('attachments', ['service_request_id' => $request->id, 'original_name' => 'evidence.jpg']);
        Storage::disk('public')->assertExists(ServiceRequest::find($request->id)->attachments()->first()->path);
    }

    public function test_student_can_consult_tracking_comments_and_cannot_see_other_request(): void
    {
        $student = $this->user();
        $other = $this->user();
        $request = $this->request($student);
        Comment::create(['service_request_id' => $request->id, 'user_id' => $other->id, 'body' => 'Actualización visible']);
        $this->actingAs($student)->get(route('requests.show', $request))->assertOk()->assertSee('Actualización visible')->assertSee('Historial cronológico');
        $this->actingAs($other)->get(route('requests.show', $request))->assertForbidden();
    }

    public function test_student_can_edit_and_cancel_only_pending_request(): void
    {
        $student = $this->user();
        $request = $this->request($student);
        $category = $this->category();
        $this->actingAs($student)->get(route('requests.edit', $request))->assertOk()->assertDontSee('name="priority"');
        $this->actingAs($student)->put(route('requests.update', $request), ['title' => 'Título editado', 'description' => 'Nueva descripción', 'category_id' => $category->id, 'location' => 'Edificio B', 'priority' => 'Urgente'])->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['id' => $request->id, 'title' => 'Título editado', 'priority' => 'Media']);
        $this->actingAs($student)->post(route('requests.cancel', $request))->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['id' => $request->id, 'status' => 'Cancelada']);
        $this->assertDatabaseHas('request_histories', ['action' => 'Cancelación']);
    }

    public function test_role_permissions_block_students_from_admin_features(): void
    {
        $student = $this->user();
        $request = $this->request($student);
        $this->actingAs($student)->get('/dashboard')->assertRedirect('/requests');
        $this->actingAs($student)->get('/reports')->assertForbidden();
        $this->actingAs($student)->get('/resources')->assertForbidden();
        $this->actingAs($student)->put(route('admin.requests.update', $request), ['status' => 'Resuelta', 'priority' => 'Alta'])->assertForbidden();
    }

    public function test_admin_can_assign_update_and_comment(): void
    {
        $admin = $this->user('administrativo');
        $student = $this->user();
        $assignee = $this->user('administrativo');
        $request = $this->request($student);
        $this->actingAs($admin)->put(route('admin.requests.update', $request), ['assigned_to' => $assignee->id, 'status' => 'En proceso', 'priority' => 'Alta'])->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['id' => $request->id, 'assigned_to' => $assignee->id, 'status' => 'En proceso', 'priority' => 'Alta']);
        $this->assertDatabaseHas('request_histories', ['action' => 'Asignación']);
        $this->actingAs($admin)->post(route('admin.requests.comments', $request), ['body' => 'Estamos trabajando en ello'])->assertRedirect();
        $this->assertDatabaseHas('comments', ['service_request_id' => $request->id, 'body' => 'Estamos trabajando en ello']);
    }

    public function test_admin_dashboard_and_reports_are_available_with_filters(): void
    {
        $admin = $this->user('administrativo');
        $student = $this->user();
        $request = $this->request($student);
        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Dashboard administrativo')->assertSee('Tecnología');
        $this->actingAs($admin)->get('/reports?status=Pendiente&priority=Media')->assertOk()->assertSee('Tiempo promedio')->assertSee($request->title);
    }

    public function test_admin_can_manage_resources(): void
    {
        $admin = $this->user('administrativo');
        $this->actingAs($admin)->post('/resources', ['name' => 'Proyector', 'type' => 'Equipo', 'location' => 'Aula 1', 'status' => 'Disponible', 'description' => 'HDMI'])->assertRedirect();
        $resource = Resource::first();
        $this->assertNotNull($resource);
        $this->actingAs($admin)->put(route('resources.update', $resource), ['name' => 'Proyector actualizado', 'type' => 'Equipo', 'location' => 'Aula 2', 'status' => 'En uso', 'description' => 'HDMI'])->assertRedirect();
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'name' => 'Proyector actualizado', 'status' => 'En uso']);
        $this->actingAs($admin)->delete(route('resources.destroy', $resource))->assertRedirect();
        $this->assertDatabaseMissing('resources',['id' => $resource->id]);
    }
}
