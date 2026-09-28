<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index()
    {
        return view('resources.index', ['resources' => Resource::latest()->paginate(12)]);
    }

    public function create()
    {
        return view('resources.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:150', 'type' => 'required|max:100', 'location' => 'required|max:150', 'status' => 'required|in:Disponible,En uso,Mantenimiento,Fuera de servicio', 'description' => 'nullable|max:1000']);
        Resource::create($data);

        return redirect()->route('resources.index')->with('success', 'Recurso creado.');
    }

    public function edit(Resource $resource)
    {
        return view('resources.edit', compact('resource'));
    }

    public function update(Request $request, Resource $resource)
    {
        $data = $request->validate(['name' => 'required|max:150', 'type' => 'required|max:100', 'location' => 'required|max:150', 'status' => 'required|in:Disponible,En uso,Mantenimiento,Fuera de servicio', 'description' => 'nullable|max:1000']);
        $resource->update($data);

        return redirect()->route('resources.index')->with('success', 'Recurso actualizado.');
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();

        return back()->with('success', 'Recurso eliminado.');
    }
}
