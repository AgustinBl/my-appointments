@extends('layouts.form', ['blob' => 'signup'])

@section('title', 'Verificar correo')
@section('aside-title', 'Falta un paso.')
@section('aside-lede', 'Verificá el correo para activar la cuenta y poder reservar tu primera cita.')

@section('content')

  <div>
    <h3>Verificá tu correo</h3>
    <p class="auth__sub">Te enviamos un enlace para activar la cuenta.</p>
  </div>

  @if (session('resent'))
    <div class="notice notice--ok">
      <span class="notice__dot">✓</span>
      <span>Enviamos un enlace nuevo a tu correo.</span>
    </div>
  @endif

  <p style="font-size: 14px; color: var(--muted); margin: 0;">
    Abrí el enlace que te llegó por correo para terminar de activar la cuenta.
    Si no lo encontrás, revisá la carpeta de correo no deseado.
  </p>

  <form method="POST" action="{{ route('verification.resend') }}">
    @csrf
    <button type="submit" class="btn btn-primary btn-block btn-lg-action">Enviar el enlace de nuevo</button>
  </form>

@endsection
