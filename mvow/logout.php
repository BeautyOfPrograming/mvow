<?php
require_once 'includes/database.php';

session_unset();
session_destroy();
redirect('login.php');
?>