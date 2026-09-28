@extends('layouts.app')
@section('content')
<div class="mb-4"><a href="{{route('requests.index')}}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a mis solicitudes</a><h1 class="page-title h3 mt-3">Nueva solicitud</h1><p class="text-secondary">Describe el requerimiento para que el equipo pueda atenderlo.</p></div><div class="card p-4"><form method="POST" action="{{route('requests.store')}}" enctype="multipart/form-data">@include('requests._form')<div class="mt-4"><button class="btn btn-primary px-4">Registrar solicitud</button></div></form></div>
@endsection
