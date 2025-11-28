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


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1 ) {
	header("Location: index.php");
	exit();
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
		<div class="inputform" style="max-width: 1400px !important;">
			<?php 
			$client_id = $_SESSION['user_id'];
			$query = "SELECT DATE(date_time) as date, DATE_FORMAT(date_time, '%H:%i') AS start_time, DATE_FORMAT(DATE_ADD(date_time, INTERVAL duration_in_minutes MINUTE), '%H:%i') AS end_time, SERVICE.name AS service_name, SERVICE.description AS service_description, CONCAT(SYSTEM_USER.name, ' ', SYSTEM_USER.surname) AS mechanic_name_surname
			FROM REGISTRATION INNER JOIN SYSTEM_USER ON REGISTRATION.fk_mechanic = SYSTEM_USER.id
			INNER JOIN SERVICE_COMPLETION ON REGISTRATION.fk_completion = SERVICE_COMPLETION.id
			INNER JOIN SERVICE ON SERVICE_COMPLETION.fk_service = SERVICE.id
			WHERE REGISTRATION.fk_client = '$client_id' AND date_time >= NOW()
			ORDER BY date_time";
			$result = mysqli_query($dbc, $query);
			$count = mysqli_num_rows($result);

			if ($count == 0) {
				echo '<div class="no-values">Neturite jokių registracijų.</div>';
			}
			else {
				echo '<table>';
				echo '
				<tr>
				<th>Data</th>
				<th>Pradžia</th>
				<th>Pabaiga</th>
				<th>Paslauga</th>
				<th>Paslaugos aprašymas</th>
				<th>Mechanikas</th>
				</tr>
				';
				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr>' .
					'<td>' . $row['date'] . '</td>' .
					'<td>' . $row['start_time'] . '</td>' .
					'<td>' . $row['end_time'] . '</td>' .
					'<td>' . $row['service_name'] . '</td>' .
					'<td>' . $row['service_description'] . '</td>' .
					'<td>' . $row['mechanic_name_surname'] . '</td>' .
					'</tr>';
				}
				echo '</table>';
			}

			?>
		</div>
	</div>

</body>
</html>

