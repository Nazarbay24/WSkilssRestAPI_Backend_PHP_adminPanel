<?php
header("Content-Type: application/json");


function route($method, $urlData, $formData) {

	if ($method === 'GET' && count($urlData) === 0) {
		global $connection;
		$events = [];

		$result = mysqli_query($connection, "SELECT * FROM `events` ORDER BY `date` ASC");

		while ($row = mysqli_fetch_assoc($result)) {
			$organizer_result = mysqli_query($connection, "SELECT * FROM `organizers` WHERE `id` = '{$row['organizer_id']}'");
			$organizer = mysqli_fetch_assoc($organizer_result);

			$event = [
				"id" => $row['id'],
				"name" => $row['name'],
				"slug" => $row['slug'],
				"date" => $row['date'],
				"organizer" => [
					"id" => $organizer['id'],
					"name" => $organizer['name'],
					"slug" => $organizer['slug']
				]
			];

			$events[] = $event;
		}

		echo json_encode([
			"events" => $events
		]);
	}
	
	else {
		http_response_code(400);
			echo json_encode([
				"message" => "Bad Request"
			]);
			return;
	}
}