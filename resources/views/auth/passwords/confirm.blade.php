@extends('layouts.form', ['blob' => 'login'])

@section('title', 'Confirmar contraseña')
@section('aside-title', 'Confirmá que sos vos.')
@section('aside-lede', 'Antes de seguir con una acción sensible, el sistema vuelve a pedirte la contraseña.')

@section('content')

  <div>
    <h3>Confirmá tu contraseña</h3>
    <p class="auth__sub">Por seguridad, volvé a escribirla para seguir.</p>
  </div>

  @if($errors->any())
    <div class="notice notice--warn">
      <span class="notice__dot">!</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('password.confirm') }}" class="stack">
    @csrf
    <div class="field">
      <label for="password">Contraseña</label>
      <input id="password" class="form-control" type="password" name="password"
             placeholder="••••••••" required autocomplete="current-password" autofocus>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Confirmar</button>
  </form>

  @if (Route::has('password.request'))
    <p class="auth__alt"><a href="{{ route('password.request') }}">Olvidé mi contraseña</a></p>
  @endif

@endsection
