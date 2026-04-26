<?php
session_start();
session_unset();
session_destroy();

/* REDIRECT TO MAIN HOME PAGE */
header("Location: ../index.php");
exit();
?>