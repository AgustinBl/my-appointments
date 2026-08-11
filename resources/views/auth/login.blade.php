@extends('layouts.form', ['blob' => 'login'])

@section('title', 'Iniciar sesión')
@section('aside-title', 'Un consultorio ordenado empieza por la agenda.')
@section('aside-lede', 'Reservá, confirmá y seguí cada cita sobre franjas de 30 minutos. Hasta 30 días hacia adelante.')

@section('content')

  <div>
    <h3>Entrá a tu cuenta</h3>
    <p class="auth__sub">Con el mail con el que te registraste.</p>
  </div>

  @if($errors->any())
    <div class="notice notice--warn">
      <span class="notice__dot">!</span>
      <span>Esas credenciales no coinciden con ninguna cuenta.</span>
    </div>
  @endif

  @if(session('status'))
    <div class="notice notice--ok">
      <span class="notice__dot">✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}" class="stack">
    @csrf

    <div class="field">
      <label for="email">Correo electrónico</label>
      <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}"
             placeholder="nombre@correo.com" required autocomplete="email" autofocus>
    </div>

    <div class="field">
      <label for="password">Contraseña</label>
      <input id="password" class="form-control" type="password" name="password"
             placeholder="••••••••" required autocomplete="current-password">
    </div>

    <div class="split">
      <label class="check">
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <span class="check__dot check__dot--box"></span>
        <span>Recordarme</span>
      </label>
      <a href="{{ route('password.request') }}" style="font-size: 13px;">Olvidé mi contraseña</a>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Entrar</button>
  </form>

  <p class="auth__alt">
    ¿Primera vez acá? <a href="{{ route('register') }}">Creá tu cuenta de paciente</a>
  </p>

@endsection
