@extends('layouts.app')
@section('content')
<div class="mb-4"><a href="{{route('resources.index')}}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a recursos</a><h1 class="page-title h3 mt-3">Editar recurso</h1></div><div class="card p-4"><form method="POST" action="{{route('resources.update',$resource)}}">@method('PUT')@include('resources._form')<button class="btn btn-primary mt-4">Guardar cambios</button></form></div>
@endsection
