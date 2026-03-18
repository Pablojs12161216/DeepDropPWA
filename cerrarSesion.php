<?php
require_once 'conexionBD.php';

session_start();
session_destroy();
header("Location: main.html");
exit;

?>