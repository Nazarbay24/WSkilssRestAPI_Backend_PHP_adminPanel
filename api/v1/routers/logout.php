<?php
header("Content-Type: application/json");


function route($method, $urlData, $formData) {


	if ($method === 'POST' && count($urlData) === 0) {
		global $connection;
		
		$token = $_GET['token'];


		$user_result = mysqli_query($connection, "SELECT * FROM `attendees` WHERE `login_token` = '$token'");

		if (mysqli_num_rows($user_result) == 1) {
			
			$user = mysqli_fetch_assoc($user_result);

			mysqli_query($connection, "UPDATE `attendees` SET `login_token` = '' WHERE `id` = '{$user['id']}'");
			
			echo json_encode([
				"message" => "Logout success"
			]);

		} else {
			http_response_code(401);
			echo json_encode([
				"message" => "Invalid token"
			]);
			return;
		}
	}

	else {
		http_response_code(400);
			echo json_encode([
				"message" => "Bad Request"
			]);
			return;
	}
}