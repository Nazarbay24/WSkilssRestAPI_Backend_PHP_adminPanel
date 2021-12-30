<?php
header("Content-Type: application/json");


function route($method, $urlData, $formData) {

	if ($method === 'GET' && count($urlData) === 0) {
		global $connection;
		$token = $formData['token'];


		$user_result = mysqli_query($connection, "SELECT * FROM `attendees` WHERE `login_token` = '$token'");



		if (mysqli_num_rows($user_result) == 1) {
			
			$user = mysqli_fetch_assoc($user_result);
			$registrations = [];

			$events_result = mysqli_query($connection, "SELECT * FROM `events` WHERE `id` 
				IN (SELECT `event_id` FROM `event_tickets` WHERE `id` 
				IN (SELECT `ticket_id` FROM `registrations` WHERE `attendee_id` = '{$user['id']}')) ORDER BY `date`");

			while ($event = mysqli_fetch_assoc($events_result)) {
				$organizer_result = mysqli_query($connection, "SELECT * FROM `organizers` WHERE `id` = '{$event['organizer_id']}'");
				$organizer = mysqli_fetch_assoc($organizer_result);

				$session_ids = [];
				$session_registrations_result = mysqli_query($connection, "SELECT * FROM `session_registrations` WHERE `registration_id` IN 
					(SELECT `id` FROM `registrations` WHERE `attendee_id` = '{$user['id']}' AND `ticket_id` IN 
					(SELECT `id` FROM `event_tickets` WHERE `event_id` = '{$event['id']}'))");

				while ($session_registrations = mysqli_fetch_assoc($session_registrations_result)) {
					$session_ids[] = $session_registrations['session_id'];
				}



				$registrations[] = [
					"event" => [
						"id" => $event['id'],
						"name" => $event['name'],
						"slug" => $event['slug'],
						"date" => $event['date'],
						"organizer" => [
							"id" => $organizer['id'],
							"name" => $organizer['name'],
							"slug" => $organizer['slug']
						],	
					],
					"session_ids" => $session_ids
				];
			}


			echo json_encode([
				"registrations" => $registrations
			]);

		} else {
			http_response_code(401);
			echo json_encode([
				"message" => "User not logged in"
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