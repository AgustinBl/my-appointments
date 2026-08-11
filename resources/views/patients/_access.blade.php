<section class="card card--raised card-pad">
  <h4>Acceso</h4>
  <p class="card-note" style="margin-bottom: 18px;">
    Con estos datos entra al sistema. La contraseña se puede cambiar después.
  </p>

  <div class="form-grid-2">
    <div class="field">
      <label for="email">Correo electrónico</label>
      <input type="email" name="email" id="email" class="form-control"
             value="{{ old('email', isset($patient) ? $patient->email : '') }}"
             placeholder="nombre@correo.com" required>
    </div>

    <div class="field">
      <label for="password">Contraseña</label>
      <div class="btn-row">
        {{-- Nunca en texto plano: se escribe oculta y se puede generar una. --}}
        <input type="password" name="password" id="password" class="form-control"
               autocomplete="new-password" {{ isset($patient) ? '' : 'required' }}>
        <button type="button" class="btn btn-secondary" id="genPassword">Generar</button>
      </div>
      <p class="field-hint" id="passwordHint">
        @if(isset($patient))
          Dejala vacía para que siga usando la actual.
        @else
          Pasásela al paciente para que entre por primera vez.
        @endif
      </p>
    </div>
  </div>
</section>
