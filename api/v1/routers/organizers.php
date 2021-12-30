<?php
header("Content-Type: application/json");


function route($method, $urlData, $formData) {

	if ($method === 'GET' && count($urlData) === 3 && $urlData[1] == 'events') {
		global $connection;
		$organizer_slug = $urlData[0];
		$event_slug = $urlData[2];

		$organizer_result = mysqli_query($connection, "SELECT * FROM `organizers` WHERE `slug` = '$organizer_slug'");
		$organizer_assoc = mysqli_fetch_assoc($organizer_result);
		$event_result = mysqli_query($connection, "SELECT * FROM `events` WHERE `slug` = '$event_slug' AND `organizer_id` = '{$organizer_assoc['id']}'");
		$event_assoc = mysqli_fetch_assoc($event_result);


		if (mysqli_num_rows($organizer_result) < 1) {
			http_response_code(404);
			echo json_encode([
				"message" => "Organizer not found"
			]);
			return;
		}
		if (mysqli_num_rows($event_result) < 1) {
			http_response_code(404);
			echo json_encode([
				"message" => "Event not found"
			]);
			return;
		}


		$event = [
			"id" => $event_assoc['id'],
			"name" => $event_assoc['name'],
			"slug" => $event_assoc['slug'],
			"date" => $event_assoc['date'],
			"channels" => [],
			"tickets" => []
		];



		$tickets_result = mysqli_query($connection, "SELECT * FROM `event_tickets` WHERE `event_id` = '{$event_assoc['id']}'");

		while ($ticket = mysqli_fetch_assoc($tickets_result)) {
			$special_validity = json_decode($ticket['special_validity']);
			$description = null;
			$available = true;

			if ($special_validity->type == 'date') {
				$month = date('F', strtotime($special_validity->date));
				$date = date_parse($special_validity->date);
				$description = 'Available until '.$month.' '.$date['day'].', '.$date['year'];

				if ($special_validity->date < date('Y-m-d')) {
					$available = false;
				}

			} elseif ($special_validity->type == 'amount') {
				$description = $special_validity->amount.' tickets available';

				$amount_check_result = mysqli_query($connection, "SELECT * FROM `registrations` WHERE `ticket_id` = '{$ticket['id']}'");


				if (mysqli_num_rows($amount_check_result) >= $special_validity->amount) {
					$available = false;
				}
			}	

			$event['tickets'][] = [
				"id" => $ticket['id'],
				"name" => $ticket['name'],
				"description" => $description,
				"cost" => $ticket['cost'],
				"available" => $available
			];
		}




		$channels_result = mysqli_query($connection, "SELECT * FROM `channels` WHERE `event_id` = '{$event_assoc['id']}'");

		$channel_index = 0;
		while ($channel = mysqli_fetch_assoc($channels_result)) {
			$event['channels'][] = [
				"id" => $channel['id'],
				"name" => $channel['name'],
				"rooms" => []
			];

			$room_index = 0;
			$rooms_result = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `channel_id` = '{$channel['id']}'");
			while ($room = mysqli_fetch_assoc($rooms_result)) {

				$event['channels'][$channel_index]['rooms'][] = [
					"id" => $room['id'],
					"name" => $room['name'],
					"sessions" => []
				];
				


				$sessions_result = mysqli_query($connection, "SELECT * FROM `sessions` WHERE `room_id` = '{$room['id']}'");
				while ($session = mysqli_fetch_assoc($sessions_result)) {
					
					$event['channels'][$channel_index]['rooms'][$room_index]['sessions'][] = [
						"id" => $session['id'],
						"title" => $session['title'],
						"description" => $session['description'],
						"speaker" => $session['speaker'],
						"start" => $session['start'],
						"end" => $session['end'],
						"type" => $session['type'],
						"cost" => $session['cost'],
					];
				}
				$room_index = $room_index + 1;
			}
			$channel_index = $channel_index + 1;
		}

		echo json_encode(
			$event
		);
	}






	if ($method === 'POST' && count($urlData) === 4 && $urlData[3] == 'registration') {
		global $connection;

		$token = $_GET['token'];

		$user_result = mysqli_query($connection, "SELECT * FROM `attendees` WHERE `login_token` = '$token'");


		if (mysqli_num_rows($user_result) == 1) {
			
			$user = mysqli_fetch_assoc($user_result);
			$ticket_id = $formData->ticket_id;
			$session_ids = $formData->session_ids;


//проверка
			$reg_check_result = mysqli_query($connection, "SELECT * FROM `registrations` WHERE `attendee_id` = '{$user['id']}' AND `ticket_id` = '$ticket_id'");
			if (mysqli_num_rows($reg_check_result) > 0) {
				http_response_code(401);
				echo json_encode([
					"message" => "User already registered"
				]);
				return;
			}


			$ticket_available_check_result = mysqli_query($connection, "SELECT * FROM `event_tickets` WHERE `id` = '$ticket_id'");
			$ticket = mysqli_fetch_assoc($ticket_available_check_result);
			$special_validity = json_decode($ticket['special_validity']);
			$available = true;


			if ($special_validity->type == 'date') {
				$date = date_parse($special_validity->date);

				if ($special_validity->date < date('Y-m-d')) {
					$available = false;

				}

			} elseif ($special_validity->type == 'amount') {
				$amount_check_result = mysqli_query($connection, "SELECT * FROM `registrations` WHERE `ticket_id` = '{$ticket['id']}'");

				if (mysqli_num_rows($amount_check_result) >= $special_validity->amount) {
					$available = false;
				}
			}


			if ($available == false) {
				http_response_code(401);
				echo json_encode([
					"message" => "Ticket is no longer available"
				]);
				return;
			}



//успешная регистрация

			mysqli_query($connection, "INSERT INTO `registrations`(`attendee_id`,`ticket_id`,`registration_time`) VALUES('{$user['id']}', '$ticket_id', NOW())");

			$registration_id = mysqli_insert_id($connection);

			if (count($session_ids) > 0) {
				foreach ($session_ids as $value) {
					$eee = mysqli_query($connection, "INSERT INTO `session_registrations`(`registration_id`,`session_id`) VALUES('$registration_id', '$value')");
				}
			}
			

			echo json_encode([
				"message" => "Registration successful"
			]);

		} else {
			http_response_code(401);
			echo json_encode([
				"message" => "User not logged in"
			]);
			return;
		}

	}



	
}