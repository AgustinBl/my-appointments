@php
  $user = auth()->user();
  $roleLabels = ['admin' => 'Recepción', 'doctor' => 'Médico', 'patient' => 'Paciente'];
  $roleLabel = $roleLabels[$user->role] ?? $user->role;
  $avatarTone = $user->role == 'patient' ? 'avatar--green' : ($user->role == 'doctor' ? 'avatar--accent' : 'avatar--light');
  $initials = collect(preg_split('/\s+/', trim($user->name)))
      ->filter()->take(2)
      ->map(function ($part) { return mb_strtoupper(mb_substr($part, 0, 1)); })
      ->implode('');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Gestión de turnos médicos: agenda, confirmaciones y horarios de atención.">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('page-title', 'Inicio') · {{ config('app.name') }}</title>
  <link href="{{ asset('img/brand/favicon.png') }}" rel="icon" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Figtree:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="{{ asset('vendor/nucleo/css/nucleo.css') }}" rel="stylesheet">
  <link href="{{ asset('css/argon.css?v=1.0.0') }}" rel="stylesheet">
  @yield('styles')
  <!-- Tema propio: se carga último para ganarle a Argon -->
  <link href="{{ asset('css/theme.css?v=3') }}" rel="stylesheet">
</head>

<body>
  <!-- Barra lateral -->
  <nav class="navbar navbar-vertical fixed-left navbar-expand-md" id="sidenav-main">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-collapse-main" aria-expanded="false" aria-label="Abrir navegación">
        <span class="navbar-toggler-icon"></span>
      </button>

      <a class="brand" href="{{ url('/home') }}">
        <span class="brand__mark" aria-hidden="true"></span>
        <span class="brand__name">{{ config('app.name') }}</span>
      </a>

      <div class="collapse navbar-collapse" id="sidenav-collapse-main">
        <span class="nav-role">{{ $roleLabel }}</span>
        @include('includes.panel.menu.'.$user->role)
      </div>

      <div class="nav-spacer"></div>

      <div class="sidebar-foot">
        <a href="{{ route('logout') }}" class="user-chip"
           onclick="event.preventDefault(); document.getElementById('formLogout').submit();"
           title="Cerrar sesión">
          <span class="avatar {{ $avatarTone }}">{{ $initials }}</span>
          <span>
            <span class="user-chip__name">{{ $user->name }}</span>
            <span class="user-chip__role">{{ $roleLabel }}</span>
          </span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="d-none" id="formLogout">@csrf</form>
      </div>
    </div>
  </nav>

  <!-- Contenido -->
  <div class="main-content">
    <header class="page-head">
      <div class="page-head__inner">
        <div>
          @hasSection('kicker')
            <span class="kicker">@yield('kicker')</span>
          @endif
          <h2>@yield('page-title', 'Inicio')</h2>
          @hasSection('page-context')
            <p class="page-context">@yield('page-context')</p>
          @endif
        </div>
        @hasSection('page-actions')
          <div class="page-head__actions">@yield('page-actions')</div>
        @endif
      </div>
    </header>

    <main class="page-body">
      @yield('content')
    </main>
  </div>

  <script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
  @yield('scripts')
  <script src="{{ asset('js/argon.js?v=1.0.0') }}"></script>
</body>
</html>
