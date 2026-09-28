<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\RequestHistory;
use App\Models\Resource;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create(['name' => 'Administrador Principal', 'email' => 'admin@campusconnect.test', 'password' => Hash::make('password'), 'role' => 'administrativo']);
        $staff = collect(['Soporte TI', 'Mantenimiento', 'Infraestructura'])->map(fn ($name, $i) => User::create(['name' => $name, 'email' => 'responsable'.($i + 1).'@campusconnect.test', 'password' => Hash::make('password'), 'role' => 'administrativo']));
        $students = collect([
            ['Ana Estudiante', 'estudiante@campusconnect.test'],
            ['Luis Fernández', 'luis@campusconnect.test'],
            ['María García', 'maria@campusconnect.test'],
            ['Diego Rojas', 'diego@campusconnect.test'],
        ])->map(fn ($student) => User::create(['name' => $student[0], 'email' => $student[1], 'password' => Hash::make('password'), 'role' => 'estudiante']));
        $categories = collect(['Infraestructura', 'Tecnología', 'Equipamiento', 'Mantenimiento', 'Otros'])->map(fn ($name) => Category::create(['name' => $name, 'slug' => str($name)->slug()]));
        foreach ([['Proyector sin señal', 'No se proyecta la imagen en el aula y se requiere revisión.', 'Edificio A · Aula 204', 'Tecnología', 'Alta', 'En proceso', 0], ['Filtración en techo', 'Hay humedad visible después de la lluvia.', 'Edificio B · Pasillo 2', 'Infraestructura', 'Urgente', 'Pendiente', 1], ['Silla dañada', 'Una silla del laboratorio está rota.', 'Edificio C · Lab 1', 'Equipamiento', 'Media', 'Resuelta', 2], ['Cambio de luminarias', 'Dos luminarias no encienden.', 'Biblioteca · Sala norte', 'Mantenimiento', 'Baja', 'Cerrada', 3], ['Acceso a software', 'Se solicita instalación de software académico.', 'Edificio A · Aula 101', 'Otros', 'Media', 'Asignada', 0]] as $row) {
            $request = ServiceRequest::create(['user_id' => $students[$row[6]]->id, 'category_id' => $categories->firstWhere('name', $row[3])->id, 'assigned_to' => in_array($row[5], ['Pendiente']) ? null : $staff[$row[6] % 3]->id, 'title' => $row[0], 'description' => $row[1], 'location' => $row[2], 'priority' => $row[4], 'status' => $row[5], 'created_at' => now()->subDays(2 + $row[6]), 'updated_at' => now()->subDays($row[6])]);
            RequestHistory::create(['service_request_id' => $request->id, 'user_id' => $request->user_id, 'action' => 'Creación', 'description' => 'Solicitud creada por el estudiante', 'created_at' => $request->created_at, 'updated_at' => $request->created_at]);
            if ($request->assigned_to) {
                RequestHistory::create(['service_request_id' => $request->id, 'user_id' => $admin->id, 'action' => 'Asignación', 'description' => 'Responsable asignado', 'new_value' => $request->assignee->name]);
            }
            if ($request->status !== 'Pendiente') {
                RequestHistory::create(['service_request_id' => $request->id, 'user_id' => $admin->id, 'action' => 'Cambio de estado', 'description' => 'Estado actualizado a '.$request->status, 'new_value' => $request->status]);
            }
            Comment::create(['service_request_id' => $request->id, 'user_id' => $admin->id, 'body' => 'Estamos dando seguimiento a tu solicitud.']);
        }
        foreach ([['Laboratorio de cómputo', 'Espacio', 'Edificio C · Piso 1', 'Disponible'], ['Proyector Epson', 'Equipo', 'Edificio A · Aula 204', 'En uso'], ['Kit de herramientas', 'Mobiliario', 'Taller de mantenimiento', 'Disponible'], ['Servidor institucional', 'Infraestructura', 'Data center', 'Mantenimiento']] as $row) {
            Resource::create(['name' => $row[0], 'type' => $row[1], 'location' => $row[2], 'status' => $row[3], 'description' => 'Recurso institucional disponible para gestión.']);
        }
    }
}
