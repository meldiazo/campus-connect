@extends('layouts.app')
@section('content')
<div class="mb-4"><a href="{{route('requests.show',$serviceRequest)}}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al detalle</a><h1 class="page-title h3 mt-3">Editar solicitud #{{$serviceRequest->id}}</h1></div><div class="card p-4"><form method="POST" action="{{route('requests.update',$serviceRequest)}}">@method('PUT')@include('requests._form')<div class="mt-4"><button class="btn btn-primary px-4">Guardar cambios</button></div></form></div>
@endsection
