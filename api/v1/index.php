<?php

$connection = mysqli_connect('localhost', 'root', '123', 'php_js');

if ($connection == false) {
	echo 'Не удалось подключиться к базе данных!<br>';
	echo mysqli_connect_error();
	exit();
}



function getFormData($method) {
	if ($method === 'GET') return $_GET;
	if ($method === 'POST') return json_decode(file_get_contents('php://input'));
}

$method = $_SERVER['REQUEST_METHOD'];
$formData = getFormData($method);

if ($method == 'OPTIONS') {
	header('HTTP/1.0 200 OK');
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type');
	return;
}

$url = $_GET['q'];
$url = rtrim($url, '/');
$urls = explode('/', $url);

$router = $urls[0];
$urlData = array_splice($urls, 1);

require_once('routers/'.$router.'.php');
route($method, $urlData, $formData);