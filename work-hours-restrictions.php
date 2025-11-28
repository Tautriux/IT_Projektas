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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$operation = htmlspecialchars($_POST['operation']);

	if ($operation == "b") {
		header("Location: users.php");
		exit();
	}
	else if ($operation == "r") {
		$mechanic_id = htmlspecialchars($_POST['mechanic_id']);

		$stmt = $dbc->prepare(
			"SELECT * FROM SYSTEM_USER 
			 WHERE id = ? AND fk_role = 2"
		);

		$stmt->bind_param("i", $mechanic_id);
		$stmt->execute();
		$check_result = $stmt->get_result();

		if ($check_result->num_rows == 1) {
			$_SESSION['limit_mechanic_id'] = $mechanic_id;
			$restriction_id = htmlspecialchars($_POST['restriction_id']);

			$stmt = $dbc->prepare("
			    DELETE FROM WORK_HOURS_RESTRICTION 
			    WHERE id = ?
			");

			$stmt->bind_param("i", $restriction_id);
			$stmt->execute();

			$deleted = $stmt->affected_rows;
			if ($deleted > 0) {
				$message = "Sėkmingai pašalintas apribojimas!";
			}
		}
	}
	else if ($operation == "a") {
		$mechanic_id = htmlspecialchars($_POST['mechanic_id']);

		$stmt = $dbc->prepare(
			"SELECT * FROM SYSTEM_USER 
			 WHERE id = ? AND fk_role = 2"
		);

		$stmt->bind_param("i", $mechanic_id);
		$stmt->execute();
		$check_result = $stmt->get_result();

		if ($check_result->num_rows == 1) {
			$_SESSION['add_limit_mechanic_id'] = $mechanic_id;
			header("Location: add-work-hours-restriction.php");
			exit();
		}
	}

}

if (!isset($_SESSION['limit_mechanic_id'])) {
	header("Location: index.php");
	exit();
}

$mechanic_id = $_SESSION['limit_mechanic_id'];
unset($_SESSION['limit_mechanic_id']);

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
		<?php if (!empty($message)): ?>
			<p class="success-message-page"><?php echo $message; ?></p>
		<?php endif; ?>

		<?php
		$query = "SELECT * FROM WORK_HOURS_RESTRICTION WHERE fk_mechanic = '$mechanic_id' ORDER BY week_day, start_time";
		$result = mysqli_query($dbc, $query);
		$count = mysqli_num_rows($result);

		echo '<div class="inputform">

		<form method="post">
		<input type="hidden" name="operation" value="b">
		<button type="submit" class="submit-button" style="width: 100%">Grįžti į vartotojų sąrašą</button>
		</form>

		</div>';

		echo '<div class="inputform">';
		if ($count == 0) {
			echo '<div class="no-values">Mechanikas neturi jokių apribojimų.</div>';
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
			<th>Šalinimas</th>
			</tr>';

			while ($row = mysqli_fetch_assoc($result)) {
				$start = date("H:i", strtotime($row['start_time']));
				$end = date("H:i", strtotime($row['end_time']));

				echo '<tr>' .
				'<td>' . $days[$row['week_day'] - 1] . '</td>' .
				'<td>' . $start . '</td>' .
				'<td>' . $end . '</td>';


				echo '<form method="post">
				<td>
				<button type="submit" class="remove-button" style="width: 100%">Šalinti</button>
				<input type="hidden" name="mechanic_id" value="' . $mechanic_id . '">
				<input type="hidden" name="restriction_id" value="' . $row['id'] . '">
				<input type="hidden" name="operation" value="r">
				</form>
				</td>';

				echo '</tr>';
			}
			echo '</table>';
		}
		echo '</div>';

		echo '<div class="inputform">
		<form method="post">
		<button type="submit" class="add-button" style="width: 100%">Pridėti</button>
		<input type="hidden" name="mechanic_id" value="' . $mechanic_id . '">
		<input type="hidden" name="operation" value="a">
		</form>
		</div>';

		?>
	</div>

</body>
</html>
