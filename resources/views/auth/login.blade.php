@extends('layouts.app')
@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#12233f,#2d6cdf)">
<div class="card p-4 p-md-5" style="width:440px"><div class="text-center mb-4"><div class="brand-mark d-inline-block mb-3 fs-3"><i class="bi bi-building"></i></div><h1 class="h3 fw-bold">Campus Connect</h1><p class="text-secondary">Gestión de solicitudes universitarias</p></div>
<form method="POST" action="{{ route('login.attempt') }}">@csrf
<label class="form-label">Correo institucional</label><input class="form-control mb-3" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@campusconnect.test">
<label class="form-label">Contraseña</label><input class="form-control mb-3" type="password" name="password" required>
<div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Recordarme</label></div>
<button class="btn btn-primary w-100 py-2">Iniciar sesión <i class="bi bi-arrow-right ms-1"></i></button></form>
<div class="small text-secondary mt-4 p-3 bg-light rounded"><strong>Demo:</strong> admin@campusconnect.test / password<br>estudiante@campusconnect.test / password</div></div></div>
@endsection
