<?php

include 'connect.php';

session_start();
// clear specific role/session keys for safety
if(isset($_SESSION['role'])) unset($_SESSION['role']);
session_unset();
session_destroy();

header('location:../home.php');

?>