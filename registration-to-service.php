<?php 
session_start();

$server = "localhost";
$db = "cars_service";
$user = "stud";
$password = "stud";

$dbc = mysqli_connect($server, $user, $password, $db);

if(!$dbc) { die ("Negaliu prisijungti prie duomenų bazės:".mysqli_error($dbc)); }

if (isset($_SESSION['user_id'])) {
	$session_user_id = $_SESSION['user_id'];
	$query = "SELECT fk_role AS role, blocked FROM SYSTEM_USER WHERE id = '$session_user_id'";
	$result = mysqli_query($dbc, $query);
	$count = mysqli_num_rows($result);

	if ($count == 0) {
		unset($_SESSION['user_id']);
		unset($_SESSION['user_role']);
		unset($_SESSION['user_blocked']);
	}
	else {
		$row = $result->fetch_assoc();
		$_SESSION['user_role'] = $row['role'];
		$_SESSION['user_blocked'] = $row['blocked'];
	}
}

if (isset($_SESSION['user_blocked']) && $_SESSION['user_blocked'] == 1) {
	header("Location: blocked.php");
	exit();
}


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
	header("Location: index.php");
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	unset($_SESSION['selected_mechanic']);
	unset($_SESSION['selected_service']);
	unset($_SESSION['selected_date']);
}

$form_submitted = false;
$message = "";

$mechanic = null;
$service= null;
$service_date = null;

function isValidDateTime($string) {
	$format = 'Y-m-d H:i:s';
	$dt = DateTime::createFromFormat($format, $string);

	if ($dt && $dt->format($format) == $string) {
		$dateTime = new DateTime($string);
		$min = new DateTime('tomorrow');
		$max = (new DateTime('tomorrow'))->modify('+29 days')->setTime(23, 59, 59);

		if ($dateTime >= $min && $dateTime <= $max) {
			return true;
		}
		else {
			return false;
		}
	}

	return false;
}

if (!empty($_POST)) {
	if (isset($_POST['mechanic'])) {
		$mechanic = htmlspecialchars($_POST['mechanic']);
		$_SESSION['selected_mechanic'] = $mechanic;
	}
	if (isset($_POST['service'])) {
		$service = htmlspecialchars($_POST['service']);
		$_SESSION['selected_service'] = $service;
	}
	if (isset($_POST['service-date'])) {
		$service_date = htmlspecialchars($_POST['service-date']);
		$_SESSION['selected_date'] = $service_date;
	}

	if (isset($_POST['service-time'])) {
		$service_time = $_POST['service-time'];
		$date_time = $_SESSION['selected_date']  . ' ' . $service_time . ':00';
		$mechanic = $_SESSION['selected_mechanic'];
		$service = $_SESSION['selected_service'];

		if (isValidDateTime($date_time)) {
			$stmt = $dbc->prepare("
				SELECT *
				FROM SYSTEM_USER
				WHERE id = ?
				AND fk_role = 2
				");

			$stmt->bind_param("i", $mechanic);
			$stmt->execute();
			$result = $stmt->get_result();
			$count = $result->num_rows;

			$stmt = $dbc->prepare("
				SELECT id, duration_in_minutes
			    FROM SERVICE_COMPLETION 
			    WHERE fk_mechanic = ? 
			    AND fk_service = ? 
			    AND removed = 0
				");

			$stmt->bind_param("ii", $mechanic, $service);
			$stmt->execute();
			$result = $stmt->get_result();
			$count2 = $result->num_rows;


			if ($count == 1 && $count2 == 1) {
				$row = $result->fetch_assoc();
				$duration_in_minutes = $row['duration_in_minutes'];
				$service_completion = $row['id'];

				$dt = new DateTime($date_time);
				$start_time = $dt->format("H:i:s");

				$dt = new DateTime($date_time);
				$dt->add(new DateInterval("PT{$duration_in_minutes}M"));
				$end_time = $dt->format("H:i:s");

				$start_ts = strtotime($start_time);
				$end_ts   = strtotime($end_time);
				$start_min = (int)$dt->format("i");

				if (($end_ts - $start_ts) <= 86340 && $start_min % 15 == 0) {
					$dt = new DateTime($date_time);
					$week_day = (int)$dt->format("N");

					$stmt = $dbc->prepare("
						SELECT *
						FROM WORK_HOURS
						WHERE fk_mechanic = ?
						AND week_day = ?
						AND ? >= start_time
						AND ? <= end_time
						");

					$stmt->bind_param("isss", $mechanic, $week_day, $start_time, $end_time);
					$stmt->execute();
					$result = $stmt->get_result();
					$count = $result->num_rows;

					$stmt = $dbc->prepare("
						SELECT 
						TIME(date_time) AS start_time,
						TIME(DATE_ADD(date_time, INTERVAL duration_in_minutes MINUTE)) AS end_time
						FROM REGISTRATION
						INNER JOIN SERVICE_COMPLETION 
						ON REGISTRATION.fk_completion = SERVICE_COMPLETION.id
						WHERE 
							REGISTRATION.fk_mechanic = ?
							AND DATE(date_time) = DATE(?)
					        AND ? > TIME(date_time)
					        AND ? < TIME(DATE_ADD(date_time, INTERVAL duration_in_minutes MINUTE))
						");

					$stmt->bind_param("isss", $mechanic, $date_time, $end_time, $start_time);
					$stmt->execute();
					$result = $stmt->get_result();
					$count2 = $result->num_rows;

					if ($count > 0 && $count2 == 0) {
						$stmt = $dbc->prepare("
							INSERT INTO REGISTRATION (date_time, fk_client, fk_mechanic, fk_completion)
							VALUES (?, ?, ?, ?)
							");

						$user_id = $_SESSION['user_id'];

						$stmt->bind_param("siii", $date_time, $user_id, $mechanic, $service_completion);
						$stmt->execute();

						$stmt = $dbc->prepare("
							UPDATE SERVICE_COMPLETION 
								SET usedInRegistration = 1 
								WHERE id = ?
								");

						$stmt->bind_param("i", $service_completion);
						$stmt->execute();

						$form_submitted = true;
						unset($_SESSION['selected_mechanic']);
						unset($_SESSION['selected_service']);
						unset($_SESSION['selected_date']);
					}

				}
			}
		}

	}

		if (!isset($_POST['service']) && !isset($_SESSION['selected_mechanic']) && !isset($_SESSION['selected_service']) && !isset($_SESSION['selected_date'])) {
			$message = "Nepasirinkta paslauga.";
		}
		else if (!isset($_POST['service']) && !isset($_POST['mechanic']) && !isset($_SESSION['selected_mechanic']) && isset($_SESSION['selected_service']) && !isset($_SESSION['selected_date'])) {
			$message = "Nepasirinktas mechanikas.";
		}
		else if (!isset($_POST['mechanic']) && !isset($_POST['service-date']) && isset($_SESSION['selected_mechanic']) && isset($_SESSION['selected_service']) && !isset($_SESSION['selected_date'])) {
			$message = "Nepasirinkta data.";
		}
		else if (!isset($_POST['service-date']) && !isset($_POST['service-time']) && isset($_SESSION['selected_mechanic']) && isset($_SESSION['selected_service']) && isset($_SESSION['selected_date'])) {
			$message = "Nepasirinktas laikas.";
		}

	}

	if (isset($_SESSION['selected_mechanic'])) {
		$mechanic = $_SESSION['selected_mechanic'];
	}
	if (isset($_SESSION['selected_service'])) {
		$service = $_SESSION['selected_service'];
	}

	if (isset($_SESSION['selected_date'])) {
		$service_date = $_SESSION['selected_date'];
	}

	?>



	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Autoservisas</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>

		<div class="menu">

			<a href="index.php" class="brand">Autoservisas</a>

			<nav class="nav" aria-label="Puslapiai">
				<?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 1): ?>
					<a href="registration-to-mechanic.php">Registracija pas meistrą</a>
					<a href="registration-to-service.php">Registracija į paslaugą</a>
					<a href="registrations.php">Mano registracijos</a>
				<?php endif; ?>

				<?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 2): ?>
					<a href="schedule.php">Mano darbo grafikas</a>
					<a href="work-hours.php">Mano darbo valandos</a>
					<a href="jobs.php">Mano darbų trukmės</a>
				<?php endif; ?>

				<?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 3): ?>
					<a href="users.php">Vartotojai</a>
					
				<?php endif; ?>

				<a href="ratings.php">Atsiliepimai apie meistrus</a>
				<a href="duk.php">DUK</a>
			</nav>

			<div class="actions">
				<?php if (isset($_SESSION['user_id'])): ?>
					<a href="logout.php" class="register">Atsijungti</a>
				<?php else: ?>
					<a href="login.php" class="login">Prisijungti</a>
					<a href="register.php" class="register">Registruotis</a>
				<?php endif; ?>
			</div>

		</div>


		<div class="content">
			<?php if (!$form_submitted): ?>
				<form method='post' class="inputform">
					<?php if (!isset($mechanic) && !isset($service) && !isset($service_date)): ?>
					<div class="input">
						<label for="service">Registracija į paslaugą</label>
						<select name="service" id="service">
							<option value="" disabled selected>Pasirinkite paslaugą iš sąrašo</option>
							<?php

							$query = "SELECT * FROM SERVICE";
							$result = mysqli_query($dbc, $query);

							if ($result && mysqli_num_rows($result) > 0) {
								while ($row = mysqli_fetch_assoc($result)) {

									echo '<option value="' . $row['id'] . '">' 
									. $row['name'] . '</option>';
								}
							} else {
								echo '<option disabled>Sistemoje nėra jokių paslaugų</option>';
							}
							?>
						</select>
					</div>

				<?php elseif (!isset($mechanic) && isset($service) && !isset($service_date)): ?>

				<div class="input">
					<label for="mechanic">Mechanikas</label>
					<select name="mechanic" id="mechanic">
						<option value="" disabled selected>Pasirinkite mechaniką iš sąrašo</option>
						<?php
						$query = "SELECT SYSTEM_USER.id as id, name, surname FROM SYSTEM_USER INNER JOIN SERVICE_COMPLETION ON SYSTEM_USER.id = SERVICE_COMPLETION.fk_mechanic WHERE SERVICE_COMPLETION.fk_service = '$service' AND removed = 0 ORDER BY name, surname ASC";
						$result = mysqli_query($dbc, $query);

						if ($result && mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option value="' . $row['id'] . '">' 
								. $row['name'] . ' ' . $row['surname'] .
								'</option>';
							}
						} else {
							echo '<option disabled>Sistemoje jokių meistrų, kurie atlieka šią paslaugą, nėra</option>';
						}
						?>
					</select>
				</div>


			<?php elseif (isset($mechanic) && isset($service) && !isset($service_date)): ?>
			<div class="input">
				<label for="service-date">Data</label>
				<select name="service-date" id="service-date">
					<option value="" disabled selected>Pasirinkite datą iš sąrašo</option>
					<?php

					$current_date = date('Y-m-d', strtotime('+1 day'));

					$dates = [];
					$date = new DateTime($current_date);

					for ($i = 0; $i < 30; $i++) {
						$flag = false;
						$weekday = (int)$date->format('N');
						$dateStr = $date->format('Y-m-d');

						$query = "SELECT week_day, start_time, end_time FROM SYSTEM_USER INNER JOIN WORK_HOURS ON SYSTEM_USER.id = WORK_HOURS.fk_mechanic WHERE fk_role=2 AND fk_mechanic='$mechanic'";
						$schedule = mysqli_query($dbc, $query);

						while ($row = mysqli_fetch_assoc($schedule)) {
							if ((int)$row['week_day'] == (int)$weekday) {
								$start = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $row['start_time']);
								$end = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $row['end_time']);

								$start_formatted = $start->format('Y-m-d H:i:s');
								$end_formatted = $end->format('Y-m-d H:i:s');

								$query = "SELECT date_time, duration_in_minutes FROM REGISTRATION 
								INNER JOIN SERVICE_COMPLETION ON REGISTRATION.fk_completion = SERVICE_COMPLETION.id 
								WHERE REGISTRATION.fk_mechanic = '$mechanic' AND DATE(date_time) = '$dateStr' AND date_time >= '$start_formatted' AND date_time <= '$end_formatted'
								ORDER BY date_time ASC";
								$result = mysqli_query($dbc, $query);
								$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

								$query = "SELECT duration_in_minutes FROM SERVICE_COMPLETION WHERE fk_mechanic='$mechanic' AND fk_service='$service' AND removed = 0";
								$service_duration_result = mysqli_query($dbc, $query);
								$service_duration_row = mysqli_fetch_assoc($service_duration_result);
								$service_completion_duration = $service_duration_row['duration_in_minutes'];

								if (count($rows) == 0) {
									$interval = $end->diff($start);
									$total_minutes = ($interval->h * 60) + $interval->i;
									if ($service_completion_duration <= $total_minutes) {
										$dates[] = $date->format('Y-m-d');
									}
									break;
								}

								$start_time = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $start->format('H:i:s'));
								$end_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[0]['date_time']);

								$index = 0;
								while ($start_time < $end) {
									$interval = $end_time->diff($start_time);
									$total_minutes = ($interval->h * 60) + $interval->i;

									if ($service_completion_duration <= $total_minutes) {
										$flag = true;
										break;
									}

									if ($index < count($rows)) {
										$start_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[$index]['date_time']);
										$start_time->modify("+{$rows[$index]['duration_in_minutes']} minutes");
									}
									else {
										break;
									}
									if ($index < count($rows) - 1) {
										$end_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[$index + 1]['date_time']);
									}
									else {
										$end_time = $end;
									}

									$index++;

								}

								if ($flag) {
									$dates[] = $date->format('Y-m-d');
									break;
								}

							}
						}
						$date->add(new DateInterval('P1D'));
					}

					if (count($dates) > 0) {
						for ($i = 0; $i < count($dates); $i++) {
							echo '<option value="' . $dates[$i] . '">' 
							. $dates[$i] .
							'</option>';
						}
					} else {
						echo '<option disabled>Nėra laisvų datų</option>';
					}
					?>
				</select>
			</div>

		<?php elseif (isset($mechanic) && isset($service) && isset($service_date)): ?>
		<div class="input">
			<label for="service-time">Laikas</label>
			<select name="service-time" id="service-time">
				<option value="" disabled selected>Pasirinkite laiką iš sąrašo</option>
				<?php
				$times = [];
				$date = new DateTime($service_date);

				$weekday = (int)$date->format('N');
				$dateStr = $date->format('Y-m-d');

				$query = "SELECT week_day, start_time, end_time FROM SYSTEM_USER INNER JOIN WORK_HOURS ON SYSTEM_USER.id = WORK_HOURS.fk_mechanic WHERE fk_role=2 AND fk_mechanic='$mechanic' ORDER BY week_day, start_time ASC";
				$schedule = mysqli_query($dbc, $query);
				while ($row = mysqli_fetch_assoc($schedule)) {
					if ((int)$row['week_day'] == (int)$weekday) {
						$start = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $row['start_time']);
						$end = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $row['end_time']);

						$start_formatted = $start->format('Y-m-d H:i:s');
						$end_formatted = $end->format('Y-m-d H:i:s');

						$query = "SELECT date_time, duration_in_minutes FROM REGISTRATION 
						INNER JOIN SERVICE_COMPLETION ON REGISTRATION.fk_completion = SERVICE_COMPLETION.id 
						WHERE REGISTRATION.fk_mechanic = '$mechanic' AND DATE(date_time) = '$dateStr' AND date_time >= '$start_formatted' AND date_time <= '$end_formatted'
						ORDER BY date_time ASC";
						$result = mysqli_query($dbc, $query);
						$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

						$query = "SELECT duration_in_minutes FROM SERVICE_COMPLETION WHERE fk_mechanic='$mechanic' AND fk_service='$service' AND removed = 0";
						$service_duration_result = mysqli_query($dbc, $query);
						$service_duration_row = mysqli_fetch_assoc($service_duration_result);
						$service_completion_duration = $service_duration_row['duration_in_minutes'];

						if (count($rows) == 0) {
							$interval = $end->diff($start);
							$total_minutes = ($interval->h * 60) + $interval->i;
							if ($service_completion_duration <= $total_minutes) {
								$remainder = $total_minutes - $service_completion_duration;
								$temp = clone $start;
								while ($remainder >= 0) {
									$times[] = $temp->format('H:i');
									$remainder = $remainder - 15;
									$temp->modify('+15 minutes');
								}
							}
						}
						else {
							$start_time = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $start->format('H:i:s'));
							$end_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[0]['date_time']);

							$index = 0;
							while ($start_time < $end) {

								$interval = $end_time->diff($start_time);
								$total_minutes = ($interval->h * 60) + $interval->i;

								if ($service_completion_duration <= $total_minutes) {
									$remainder = $total_minutes - $service_completion_duration;
									$temp = clone $start_time;
									while ($remainder >= 0) {
										$times[] = $temp->format('H:i');
										$remainder = $remainder - 15;
										$temp->modify('+15 minutes');
									}
								}

								if ($index < count($rows)) {
									$start_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[$index]['date_time']);
									$start_time->modify("+{$rows[$index]['duration_in_minutes']} minutes");
								}
								else {
									break;
								}
								if ($index < count($rows) - 1) {
									$end_time = DateTime::createFromFormat('Y-m-d H:i:s', $rows[$index + 1]['date_time']);
								}
								else {
									$end_time = $end;
								}
								$index++;
							}
						}
					}
				}

				if (count($times) > 0) {
					for ($i = 0; $i < count($times); $i++) {
						echo '<option value="' . $times[$i] . '">' 
						. $times[$i] .
						'</option>';
					}
				} else {
					echo '<option disabled>Nėra laisvų laikų</option>';
				}
				?>
			</select>
		</div>
	<?php endif; ?>

	<input type='submit' name='choose' value='Pasirinkti' class="submit-button">

	<?php if (!empty($message)): ?>
		<p class="error-message"><?php echo $message; ?></p>
	<?php endif; ?>
</form>


<?php else: ?>
	<div class="inputform">
		<div class="success-message-page">Registracija pas meistrą sėkminga!</div>
		<a href="registration-to-service.php" class="submit-button">Registruotis dar kartą</a>
	</div>
<?php endif; ?>
</div>


</body>
</html>
