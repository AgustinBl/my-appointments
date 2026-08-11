<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Gestión de turnos médicos: agenda, confirmaciones y horarios de atención.">
  <title>@yield('title', 'Acceso') · {{ config('app.name') }}</title>
  <link href="{{ asset('img/brand/favicon.png') }}" rel="icon" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Figtree:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="{{ asset('vendor/nucleo/css/nucleo.css') }}" rel="stylesheet">
  <link type="text/css" href="{{ asset('css/argon.css?v=1.0.0') }}" rel="stylesheet">
  <!-- Tema propio: se carga último para ganarle a Argon -->
  <link href="{{ asset('css/theme.css?v=3') }}" rel="stylesheet">
</head>

<body>
  <div class="auth">
    <aside class="auth__aside">
      <span class="auth__blob auth__blob--{{ $blob ?? 'login' }}" aria-hidden="true"></span>

      <a class="brand" href="{{ url('/') }}">
        <span class="brand__mark" aria-hidden="true"></span>
        <span class="brand__name">{{ config('app.name') }}</span>
      </a>

      <div>
        <h2 class="auth__display">@yield('aside-title')</h2>
        <p class="auth__lede">@yield('aside-lede')</p>
      </div>

      <span class="auth__foot">Consultorio · Turnos</span>
    </aside>

    <main class="auth__main">
      <div class="auth__form">
        @yield('content')
      </div>
    </main>
  </div>

  <script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
