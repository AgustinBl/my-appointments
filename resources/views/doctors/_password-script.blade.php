<script>
// Genera una contraseña legible y la muestra para poder copiarla.
$(function () {
  $('#genPassword').on('click', function () {
    const alphabet = 'abcdefghijkmnopqrstuvwxyz23456789';
    let value = '';
    for (let i = 0; i < 10; i++) {
      value += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
    }

    $('#password').attr('type', 'text').val(value).focus().select();
    $('#passwordHint').text('Copiala antes de guardar: después no se vuelve a mostrar.');
  });
});
</script>
