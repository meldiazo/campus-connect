<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\RequestHistory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    private array $priorities = ['Baja', 'Media', 'Alta', 'Urgente'];

    private array $statuses = ['Pendiente', 'Asignada', 'En proceso', 'Resuelta', 'Cerrada', 'Cancelada'];

    public function index(Request $request)
    {
        $query = ServiceRequest::with(['category', 'assignee', 'student'])->latest();
        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        } else {
            foreach (['status', 'priority', 'category_id'] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->input($filter));
                }
            }
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->input('from'));
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->input('to'));
            }
        }
        $requests = $query->paginate(12)->withQueryString();

        return view('requests.index', ['requests' => $requests, 'categories' => Category::orderBy('name')->get(), 'statuses' => $this->statuses, 'priorities' => $this->priorities]);
    }

    public function create()
    {
        abort_unless(! auth()->user()->isAdmin(), 403);

        return view('requests.create', ['categories' => Category::orderBy('name')->get(), 'priorities' => $this->priorities]);
    }

    public function store(Request $request)
    {
        abort_unless(! auth()->user()->isAdmin(), 403);
        $data = $request->validate(['title' => 'required|string|max:150', 'description' => 'required|string', 'category_id' => 'required|exists:categories,id', 'location' => 'required|string|max:150', 'evidence' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx']);

        return DB::transaction(function () use ($data, $request) {
            $serviceRequest = ServiceRequest::create([...collect($data)->except('evidence')->toArray(), 'user_id' => auth()->id(), 'priority' => 'Media', 'status' => 'Pendiente']);
            $this->history($serviceRequest, 'Creación', 'Solicitud creada');
            if ($request->hasFile('evidence')) {
                $this->storeAttachment($serviceRequest, $request);
            }

            return redirect()->route('requests.show', $serviceRequest)->with('success', 'Solicitud creada correctamente.');
        });
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequest($serviceRequest);
        $serviceRequest->load(['student', 'category', 'assignee', 'attachments.uploader', 'comments.user', 'histories.user']);

        return view('requests.show', compact('serviceRequest'));
    }

    public function edit(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequest($serviceRequest);
        abort_unless(! auth()->user()->isAdmin() && $serviceRequest->status === 'Pendiente', 403);

        return view('requests.edit', ['serviceRequest' => $serviceRequest, 'categories' => Category::orderBy('name')->get(), 'priorities' => $this->priorities]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeRequest($serviceRequest);
        abort_unless(! auth()->user()->isAdmin() && $serviceRequest->status === 'Pendiente', 403);
        $data = $request->validate(['title' => 'required|string|max:150', 'description' => 'required|string', 'category_id' => 'required|exists:categories,id', 'location' => 'required|string|max:150']);
        $serviceRequest->update($data);

        return redirect()->route('requests.show', $serviceRequest)->with('success', 'Solicitud actualizada.');
    }

    public function cancel(ServiceRequest $serviceRequest)
    {
        $this->authorizeRequest($serviceRequest);
        abort_unless(! auth()->user()->isAdmin() && $serviceRequest->status === 'Pendiente', 403);
        $old = $serviceRequest->status;
        $serviceRequest->update(['status' => 'Cancelada']);
        $this->history($serviceRequest, 'Cancelación', 'La solicitud fue cancelada por el estudiante', $old, 'Cancelada');

        return back()->with('success', 'Solicitud cancelada.');
    }

    public function attach(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeRequest($serviceRequest);
        $request->validate(['evidence' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx']);
        $this->storeAttachment($serviceRequest, $request);

        return back()->with('success', 'Evidencia adjuntada.');
    }

    public function adminUpdate(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['assigned_to' => 'nullable|exists:users,id', 'status' => 'required|in:'.implode(',', $this->statuses), 'priority' => 'required|in:'.implode(',', $this->priorities)]);
        if ($data['assigned_to'] && ! User::where('id', $data['assigned_to'])->where('role', 'administrativo')->exists()) {
            return back()->withErrors(['assigned_to' => 'El responsable debe ser administrativo.']);
        }
        $oldAssignee = $serviceRequest->assigned_to;
        $oldStatus = $serviceRequest->status;
        $oldPriority = $serviceRequest->priority;
        $serviceRequest->update($data);
        if ($oldAssignee != $data['assigned_to']) {
            $this->history($serviceRequest, 'Asignación', 'Se actualizó el responsable', optional(User::find($oldAssignee))->name, ' '.optional(User::find($data['assigned_to']))->name);
        }
        if ($oldStatus !== $data['status']) {
            $this->history($serviceRequest, 'Cambio de estado', "Estado cambiado de {$oldStatus} a {$data['status']}", $oldStatus, $data['status']);
        }
        if ($oldPriority !== $data['priority']) {
            $this->history($serviceRequest, 'Cambio de prioridad', "Prioridad cambiada de {$oldPriority} a {$data['priority']}", $oldPriority, $data['priority']);
        }

        return back()->with('success', 'Gestión de solicitud actualizada.');
    }

    public function comment(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['body' => 'required|string|max:2000']);
        Comment::create(['service_request_id' => $serviceRequest->id, 'user_id' => auth()->id(), 'body' => $data['body']]);

        return back()->with('success', 'Comentario registrado.');
    }

    private function authorizeRequest(ServiceRequest $serviceRequest): void
    {
        abort_unless(auth()->user()->isAdmin() || $serviceRequest->user_id === auth()->id(), 403);
    }

    private function history(ServiceRequest $request, string $action, string $description, ?string $old = null, ?string $new = null): void
    {
        RequestHistory::create(['service_request_id' => $request->id, 'user_id' => auth()->id(), 'action' => $action, 'description' => $description, 'old_value' => $old, 'new_value' => $new]);
    }

    private function storeAttachment(ServiceRequest $serviceRequest, Request $request): void
    {
        $file = $request->file('evidence');
        $path = $file->store('evidence', 'public');
        $serviceRequest->attachments()->create(['uploaded_by' => auth()->id(), 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
    }
}
