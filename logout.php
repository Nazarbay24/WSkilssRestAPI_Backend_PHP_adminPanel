<?php
    require_once('db.php');

    unset($_SESSION['logged']);

    header('Location: /');
?>