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

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2 ) {
	header("Location: index.php");
	exit();
}

$mechanic_id = $_SESSION['user_id'];

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
		<?php
		$query = "SELECT * FROM WORK_HOURS_RESTRICTION WHERE fk_mechanic = '$mechanic_id' ORDER BY week_day, start_time";
		$result = mysqli_query($dbc, $query);
		$count = mysqli_num_rows($result);

		echo '<div class="inputform">
		<a href="work-hours.php" class="submit-button">Grįžti į darbo valandų sąrašą</a>
		</div>';

		echo '<div class="inputform">';
		if ($count == 0) {
			echo '<div class="no-values">Jūs neturite jokių darbo laiko apribojimų.</div>';
		}
		else {
			$days = [
			    "Pirmadienis",
			    "Antradienis",
			    "Trečiadienis",
			    "Ketvirtadienis",
			    "Penktadienis",
			    "Šeštadienis",
			    "Sekmadienis"
			];

			echo '<table>';
			echo '
			<tr>
			<th>Savaitės diena</th>
			<th>Pradžios laikas</th>
			<th>Pabaigos laikas</th>
			</tr>';

			while ($row = mysqli_fetch_assoc($result)) {
				$start = date("H:i", strtotime($row['start_time']));
				$end = date("H:i", strtotime($row['end_time']));

				echo '<tr>' .
				'<td>' . $days[$row['week_day'] - 1] . '</td>' .
				'<td>' . $start . '</td>' .
				'<td>' . $end . '</td>' .
				'</tr>';
			}
			echo '</table>';
		}
		echo '</div>';
		?>
	</div>

</body>
</html>

