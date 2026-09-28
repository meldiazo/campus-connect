# Campus Connect

Aplicación web Laravel 12 + Blade para centralizar solicitudes universitarias de mantenimiento, soporte, infraestructura y servicios institucionales. La aplicación usa PostgreSQL en producción/desarrollo y SQLite en la suite automatizada.

## Instalación

Requisitos: PHP 8.2+, Composer, PostgreSQL 14+ y Node/NPM (opcional para assets adicionales).

    git clone <url-del-repositorio>
    cd campus-connect
    composer install
    cp .env.example .env
    php artisan key:generate

Crea una base PostgreSQL llamada campus_connect y ajusta en .env DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME y DB_PASSWORD. Luego:

    php artisan migrate --seed
    php artisan storage:link
    php artisan serve

Abre http://localhost:8000.

## Credenciales demo

- Administrativo: admin@campusconnect.test / password
- Estudiante: estudiante@campusconnect.test / password
- Responsables adicionales: responsable1@campusconnect.test, responsable2@campusconnect.test, responsable3@campusconnect.test / password

El seeder crea categorías, cinco solicitudes en estados y prioridades diferentes, comentarios, historiales y recursos institucionales.

## Pruebas

La suite usa SQLite en memoria:

    php artisan test

El workflow .github/workflows/tests.yml ejecuta las pruebas en cada push y pull_request usando PostgreSQL, después de instalar dependencias y migrar.

## Casos de uso implementados

| Caso | Pantalla/ruta demostrable |
|---|---|
| CU01 Iniciar sesión | /login |
| CU02 Cerrar sesión | Botón Salir del navbar (POST /logout) |
| CU03 Crear solicitud | Nueva solicitud → /requests/create |
| CU04 Adjuntar evidencia | Formulario de evidencia en /requests/{id} |
| CU05 Consultar mis solicitudes | Mis solicitudes → /requests como estudiante |
| CU06 Consultar seguimiento | /requests/{id}, historial cronológico |
| CU07 Editar solicitud | Botón Editar en detalle mientras está Pendiente |
| CU08 Cancelar solicitud | Botón Cancelar en detalle mientras está Pendiente |
| CU09 Visualizar comentarios | Panel Comentarios en el detalle |
| CU10 Consultar todas las solicitudes | Solicitudes → /requests como administrativo |
| CU11 Asignar responsable | Panel Gestionar solicitud en /requests/{id} |
| CU12 Cambiar estado | Panel Gestionar solicitud |
| CU13 Asignar prioridad | Panel Gestionar solicitud |
| CU14 Registrar comentario | Formulario Registrar comentario en el detalle administrativo |
| CU15 Visualizar dashboard | /dashboard como administrativo |
| CU16 Generar reportes | /reports, filtros y métricas de atención |
| CU17 Gestionar recursos | /resources, alta, edición y eliminación |

La prioridad de una solicitud nueva se establece automáticamente en Media. Solo el personal administrativo puede modificarla desde la gestión de solicitudes.

La autorización combina middleware role y comprobaciones de propietario: estudiantes solo ven y modifican sus propias solicitudes, mientras que las operaciones administrativas requieren el rol administrativo.
