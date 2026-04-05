<?php
session_start();
session_destroy();
header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/index.php');
exit();
?>