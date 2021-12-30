<?php
header("Content-Type: application/json");


function route($method, $urlData, $formData) {

	if ($method === 'POST' && count($urlData) === 0) {
		global $connection;

		$lastname = $formData->lastname;
		$registration_code = $formData->registration_code;


		$user_result = mysqli_query($connection, "SELECT * FROM `attendees` WHERE `lastname` = '$lastname' AND `registration_code` = '$registration_code'");

		if (mysqli_num_rows($user_result) == 1) {
			
			$user = mysqli_fetch_assoc($user_result);
			$token = md5($user['username']);

			mysqli_query($connection, "UPDATE `attendees` SET `login_token` = '$token' WHERE `id` = '{$user['id']}'");
			
			echo json_encode([
				"firstname" => $user['firstname'],
				"lastname" => $user['lastname'],
				"username" => $user['username'],
				"email" => $user['email'],
				"token" => $token
			]);

		} else {
			http_response_code(401);
			echo json_encode([
				"message" => "Invalid login"
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