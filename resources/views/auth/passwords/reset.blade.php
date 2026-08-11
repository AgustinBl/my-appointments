@extends('layouts.form', ['blob' => 'login'])

@section('title', 'Nueva contraseña')
@section('aside-title', 'Elegí una contraseña nueva.')
@section('aside-lede', 'Después de guardarla entrás con ella. El enlace de recuperación deja de servir.')

@section('content')

  <div>
    <h3>Nueva contraseña</h3>
    <p class="auth__sub">Escribila dos veces para confirmar.</p>
  </div>

  @if($errors->any())
    <div class="notice notice--warn">
      <span class="notice__dot">!</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('password.update') }}" class="stack">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="field">
      <label for="email">Correo electrónico</label>
      <input id="email" class="form-control" type="email" name="email"
             value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
    </div>

    <div class="field">
      <label for="password">Nueva contraseña</label>
      <input id="password" class="form-control" type="password" name="password"
             placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
    </div>

    <div class="field">
      <label for="password-confirm">Repetí la contraseña</label>
      <input id="password-confirm" class="form-control" type="password" name="password_confirmation"
             required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Guardar la contraseña</button>
  </form>

@endsection
