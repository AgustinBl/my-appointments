@extends('layouts.form', ['blob' => 'signup'])

@section('title', 'Crear cuenta')
@section('aside-title', 'Tres datos y ya podés reservar.')
@section('aside-lede', 'La cuenta es de paciente. Si sos médico o trabajás en recepción, te la crea la administración del consultorio.')

@section('content')

  <div>
    <h3>Creá tu cuenta</h3>
    <p class="auth__sub">Vas a poder reservar y cancelar tus citas.</p>
  </div>

  @if($errors->any())
    <div class="notice notice--warn">
      <span class="notice__dot">!</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('register') }}" class="stack">
    @csrf

    <div class="field">
      <label for="name">Nombre y apellido</label>
      <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}"
             placeholder="Lucía Ferreyra" required autocomplete="name" autofocus>
    </div>

    <div class="field">
      <label for="email">Correo electrónico</label>
      <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}"
             placeholder="lucia@ejemplo.com" required autocomplete="email">
    </div>

    <div class="field">
      <label for="password">Contraseña</label>
      <input id="password" class="form-control" type="password" name="password"
             placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
    </div>

    <div class="field">
      <label for="password_confirmation">Repetí la contraseña</label>
      <input id="password_confirmation" class="form-control" type="password" name="password_confirmation"
             required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Crear cuenta</button>
  </form>

  <p class="auth__alt">
    ¿Ya tenés cuenta? <a href="{{ route('login') }}">Iniciá sesión</a>
  </p>

@endsection
