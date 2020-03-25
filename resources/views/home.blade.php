@extends('layouts.panel')

@section('content')

<div class="row justify-content-md-center align-items-center">
     <div class="col-md-8 mb-4 mt-8">
            <div class="card w-75 border-primary" >
                

                <div class="card-body text-primary text-center ">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    Bienvenido!
                </div>
            </div>
        </div>

        @if (auth()->user()->role == 'admin')
          <div class="col-xl-8 mb-6 mb-xl-0 mt-5">
            <div class="card w-75 bg-gradient-primary shadow">
              <div class="card-header bg-transparent">
                <div class="row align-items-center">
                  <div class="col">
                    <h6 class="text-uppercase text-light ls-1 mb-1">Notificación general</h6>
                    <h2 class="text-white mb-0">Enviar a todos los usuarios</h2>
                  </div>
                </div>
              </div>
              <div class="card-body">
                
                @if(session('notification'))
                <div class="alert alert-success" role="alert">
                  {{ session('notification') }}
                </div>
                @endif
                
                <form action="{{ url('/fcm/send') }}" method="post">
                  @csrf
                  <div class="form-group">
                    <label for="body" class="text-white" >Título</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                  <div class="form-group">
                    <label for="body" class="text-white">Mensaje</label>
                    <textarea name="body" class="form-control" id="body" rows="2" required></textarea>
                  </div>
                  <div class="row justify-content-center">
                  <button class="btn btn-success btn-lg btn-block">Enviar notificación</button>
                </div>
                </form>
              </div>
            </div>
          </div>
        @endif
 </div>
@endsection
