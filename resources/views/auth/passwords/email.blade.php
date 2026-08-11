@extends('layouts.form', ['blob' => 'login'])

@section('title', 'Recuperar contraseña')
@section('aside-title', 'Volvé a entrar en dos pasos.')
@section('aside-lede', 'Te mandamos un enlace al correo con el que entrás al sistema. Desde ahí elegís una contraseña nueva.')

@section('content')

  <div>
    <h3>Recuperar contraseña</h3>
    <p class="auth__sub">Te enviamos un enlace para elegir una nueva.</p>
  </div>

  @if (session('status'))
    <div class="notice notice--ok">
      <span class="notice__dot">✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="notice notice--warn">
      <span class="notice__dot">!</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('password.email') }}" class="stack">
    @csrf
    <div class="field">
      <label for="email">Correo electrónico</label>
      <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}"
             placeholder="nombre@correo.com" required autocomplete="email" autofocus>
      <p class="field-hint">Usá el mismo correo con el que entrás al sistema.</p>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Enviar el enlace</button>
  </form>

  <p class="auth__alt"><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>

@endsection
