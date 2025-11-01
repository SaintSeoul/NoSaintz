<?php

include 'connect.php';

session_start();
// clear role and admin specific session keys
if(isset($_SESSION['role'])) unset($_SESSION['role']);
if(isset($_SESSION['admin_id'])) unset($_SESSION['admin_id']);
session_unset();
session_destroy();

header('location:../login.php');

?>