<?php
session_start();
session_destroy();
echo "Byl jsi odhlášen"."<br>\n";
echo '<a href="login.php">Přihlásit se</a>';

?>