<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Campus Connect' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--cc-navy:#12233f;--cc-blue:#2d6cdf;--cc-bg:#f4f7fb}body{background:var(--cc-bg);color:#24324a}.navbar{background:var(--cc-navy)}.brand-mark{background:#5ec5b7;border-radius:10px;padding:.4rem .55rem;color:#10283e}.sidebar{background:#fff;min-height:calc(100vh - 56px);border-right:1px solid #e7ebf2}.sidebar .nav-link{color:#66738a;border-radius:10px;margin:.2rem 0}.sidebar .nav-link.active,.sidebar .nav-link:hover{background:#eaf1ff;color:var(--cc-blue)}.card{border:0;border-radius:16px;box-shadow:0 4px 16px rgba(18,35,63,.06)}.stat-card{border-left:4px solid var(--cc-blue)}.table> :not(caption)>*>*{padding:1rem .75rem}.badge{font-weight:600}.badge-status{background:#e8eef9;color:#385276}.badge-priority{background:#fff0d9;color:#a26300}.badge-urgent{background:#ffe1e6;color:#b4233c}.timeline{border-left:2px solid #dbe5f3;margin-left:12px;padding-left:24px}.timeline-item{position:relative;margin-bottom:1.4rem}.timeline-item:before{content:'';position:absolute;left:-32px;top:4px;width:12px;height:12px;background:var(--cc-blue);border:3px solid #dbe5f3;border-radius:50%}.form-control,.form-select{border-radius:10px;padding:.7rem .85rem}.btn{border-radius:9px}.page-title{font-weight:750;color:var(--cc-navy)}
    </style>
</head>
<body>
@auth
<nav class="navbar navbar-dark sticky-top"><div class="container-fluid px-4"><a class="navbar-brand fw-bold" href="{{ route('dashboard') }}"><span class="brand-mark me-2"><i class="bi bi-building"></i></span>Campus Connect</a><div class="d-flex align-items-center gap-3 text-white"><span class="small d-none d-md-inline">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right me-1"></i>Salir</button></form></div></div></nav>
<div class="container-fluid"><div class="row">
<aside class="col-md-2 col-lg-2 p-3 sidebar d-none d-md-block"><div class="small text-uppercase fw-bold text-secondary mb-2">Navegación</div><nav class="nav flex-column">
<a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a>
<a class="nav-link {{ request()->routeIs('requests.*')?'active':'' }}" href="{{ route('requests.index') }}"><i class="bi bi-inbox me-2"></i>{{ auth()->user()->isAdmin()?'Solicitudes':'Mis solicitudes' }}</a>
@if(!auth()->user()->isAdmin())<a class="nav-link" href="{{ route('requests.create') }}"><i class="bi bi-plus-circle me-2"></i>Nueva solicitud</a>@endif
@if(auth()->user()->isAdmin())<a class="nav-link {{ request()->routeIs('reports.*')?'active':'' }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart me-2"></i>Reportes</a><a class="nav-link {{ request()->routeIs('resources.*')?'active':'' }}" href="{{ route('resources.index') }}"><i class="bi bi-box-seam me-2"></i>Recursos</a>@endif
</nav></aside>
<main class="col-md-10 col-lg-10 p-4"><div class="d-md-none mb-3"><a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">Solicitudes</a></div>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Revisa los datos:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main></div></div>
@else @yield('content') @endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
