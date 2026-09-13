<?php
session_start();
session_unset();
session_destroy();

// Elimina la cookie de "recordar" usuario.
if (isset($_COOKIE['recordar_token'])) {
    setcookie("recordar_token", "", time() - 3600, "/");
}

header("Location: index.php");
exit();
?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
  window.onload = function () {
    if (typeof google !== 'undefined') {
      google.accounts.id.disableAutoSelect();
    }
  };
</script>