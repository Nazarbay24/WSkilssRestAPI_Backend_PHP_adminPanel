<?php

$connection = mysqli_connect('localhost', 'root', '123', 'php_js');

if ($connection == false) {
	echo 'Не удалось подключиться к базе данных!<br>';
	echo mysqli_connect_error();
	exit();
}
//qSW7v7o!#J7DwBUL

session_start();