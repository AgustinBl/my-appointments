@php
  // `Reservada` es el nombre interno; en la interfaz siempre se dice «Por confirmar».
  $states = [
    'Reservada'  => ['tag--accent',  'Por confirmar'],
    'Confirmada' => ['tag--green',   'Confirmada'],
    'Atendida'   => ['tag--neutral', 'Atendida'],
    'Cancelada'  => ['tag--outline', 'Cancelada'],
  ];
  $state = $states[$status] ?? ['tag--neutral', $status];
@endphp
<span class="tag {{ $state[0] }}">{{ $state[1] }}</span>
