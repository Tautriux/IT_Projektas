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
$message = "";
$errorMessage = "";

if($_POST != null && isset($_POST['changed'])) {
	$changed_value = htmlspecialchars($_POST['changed']);
	$parts = explode("|", $changed_value);

	if (count($parts) == 5) {
		$start_or_end = $parts[0];
		$start_time = $parts[1];
		$end_time = $parts[2];
		$id = $parts[3];
		$week_day = $parts[4];

		$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

		if (preg_match($timePattern, $start_time) &&
		    preg_match($timePattern, $end_time)) {


			$start = DateTime::createFromFormat('H:i', $start_time);
		    $end   = DateTime::createFromFormat('H:i', $end_time);

		    $stmt = $dbc->prepare("
		    	SELECT *
				FROM WORK_HOURS
				WHERE fk_mechanic = ?
				  AND week_day = ?
				  AND ? > start_time
				  AND ? < end_time
			");

			$stmt->bind_param("isss", $mechanic_id, $week_day, $end_time, $start_time);
			$stmt->execute();

			$result = $stmt->get_result();
			$count = $result->num_rows;

		    if ($start <= $end && $count == 0) {

		    	$stmt = $dbc->prepare("
				    SELECT *
				    FROM WORK_HOURS_RESTRICTION
				    WHERE fk_mechanic = ?
				      AND week_day = ?
				      AND ? > start_time
				      AND ? < end_time
				      AND ? != id
				");

				$stmt->bind_param("isssi", $mechanic_id, $week_day, $end_time, $start_time, $id);
				$stmt->execute();

				$result = $stmt->get_result();
				$count = $result->num_rows;

				if ($count != 0) {
					$errorMessage = "Laukelio reikšmė negali būti atnaujinta į tokią, nes ji kertasi su jums uždėtu apribojimo intervalu!";
				} else {

				    if ($start_or_end == 1) {
				        $message = "Sėkmingai atnaujinta darbo pradžios laukelio reikšmė.";

				        $stmt = $dbc->prepare("
				            UPDATE WORK_HOURS
				            SET start_time = ?
				            WHERE id = ? AND fk_mechanic = ?
				        ");
				        $stmt->bind_param("sii", $start_time, $id, $mechanic_id);
				    } else {
				        $message = "Sėkmingai atnaujinta darbo pabaigos laukelio reikšmė.";

				        $stmt = $dbc->prepare("
				            UPDATE WORK_HOURS
				            SET end_time = ?
				            WHERE id = ? AND fk_mechanic = ?
				        ");
				        $stmt->bind_param("sii", $end_time, $id, $mechanic_id);
				    }

				    $stmt->execute();
				}
		    }
		}
	}
	else if (count($parts) == 2) {
		$add_or_delete = $parts[0];
		$value = $parts[1];

		if ($add_or_delete == 1) {
			if ($value >= 1 && $value <= 7) {
				$time = '00:00:00';

				$stmt = $dbc->prepare("
				    SELECT MAX(end_time) AS time
				    FROM WORK_HOURS
				    WHERE week_day = ? AND fk_mechanic = ?
				");

				$stmt->bind_param("si", $value, $mechanic_id);
				$stmt->execute();
				$result = $stmt->get_result();
				$count = $result->num_rows;
				$row = $result->fetch_assoc();

				if ($row && $row['time'] != null) {
					$time = $row['time'];
				}

				$stmt = $dbc->prepare("
				    SELECT end_time
				    FROM WORK_HOURS_RESTRICTION
				    WHERE week_day = ? AND fk_mechanic = ? AND start_time = ?
				    LIMIT 1
				");

				$stmt->bind_param("sis", $value, $mechanic_id, $time);
				$stmt->execute();
				$result = $stmt->get_result();
				$count = $result->num_rows;

				if ($count != 0) {
					$row = $result->fetch_assoc();
					$time = $row['end_time'];
				}

				$stmt = $dbc->prepare("
				    INSERT INTO WORK_HOURS (week_day, start_time, end_time, fk_mechanic)
				    VALUES (?, ?, ?, ?)
				");

				$stmt->bind_param("issi", $value, $time, $time, $mechanic_id);
				$stmt->execute();

				$message = "Sėkmingai pridėtos darbo valandos.";

			}
		}
		else {
			$message = "Sėkmingai pašalintos darbo valandos.";
			$stmt = $dbc->prepare("
			    DELETE FROM WORK_HOURS
			    WHERE id = ? AND fk_mechanic = ?
			");

			$stmt->bind_param("ii", $value, $mechanic_id);
			$stmt->execute();
		}
		
	}
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
		<?php if (!empty($message)): ?>
			<p class="success-message-page"><?php echo $message; ?></p>
		<?php endif; ?>
		<?php if (!empty($errorMessage)): ?>
			<p class="error-message"><?php echo $errorMessage; ?></p>
		<?php endif; ?>


			<?php

			$weekdaysNames = [
				"Pirmadienis",
				"Antradienis",
				"Trečiadienis",
				"Ketvirtadienis",
				"Penktadienis",
				"Šeštadienis",
				"Sekmadienis"
			];

			for ($i = 1; $i <= 7; $i++) {
				echo '<form method="post" class="inputform">';

				echo '
				<input type="hidden" name="changed" value="">
				<div class="weekday" style="padding-top: 0px !important;">
				<div class="text">' . $weekdaysNames[$i - 1] . '</div>
				<button type="button" class="add-btn" onclick="this.form.changed.value=\'1|' . $i . '\'; this.form.submit()">+</button>
				</div>';

				$query = "SELECT * FROM WORK_HOURS WHERE week_day = '$i' AND fk_mechanic = '$mechanic_id' ORDER BY start_time";
				$result = mysqli_query($dbc, $query);

				$start = [];
				$end = [];
				$id = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$start[] = substr($row['start_time'], 0, 5);
					$end[] = substr($row['end_time'], 0, 5);
					$id[] = $row['id'];
				}
 				
 				if (count($start) == 0) {
 					echo '<div class="no-values">Jokių laikų sukurta nėra.</div>';
 				}



				for($c = 0; $c < count($start); $c++) {
					echo '<div class="work-hours">
					<div class="text">nuo</div>

					<div class="input">
					<select onchange="this.form.changed.value=this.value; this.form.submit()" required>';

					if ($c - 1 >= 0) {
						list($hours, $minutes) = explode(":", $end[$c - 1]);
						$time= ($hours * 60) + $minutes;
					}
					else {
						$time = 0;
					}

					list($hours, $minutes) = explode(":", $end[$c]);
					$max_time = ($hours * 60) + $minutes;


					while ($time <= $max_time) {
						$hours = intdiv($time, 60);
						$mins  = $time % 60;

						$timeString = sprintf('%02d:%02d', $hours, $mins);

						if ($timeString == $start[$c]) {
							echo '<option value="1|' . $timeString . '|' . $end[$c] . '|' . $id[$c] . '|' . $i . '" selected>' . $timeString . '</option>';
						}
						else {
							echo '<option value="1|' . $timeString . '|' . $end[$c] . '|' . $id[$c] . '|' . $i . '">' . $timeString . '</option>';
						}
						$time += 15;
					}

					
					echo '</select>
					</div>

					<div class="text">iki</div>

					<div class="input">
					<select onchange="this.form.changed.value=this.value; this.form.submit()" required>';


					list($hours, $minutes) = explode(":", $start[$c]);
					$time = ($hours * 60) + $minutes;

					if ($c + 1 < count($start)) {
						list($hours, $minutes) = explode(":", $start[$c + 1]);
						$next = ($hours * 60) + $minutes + 15;
					}
					else {
						$next = 1440;
					}

					while ($time < $next) {
						$hours = intdiv($time, 60);
						$mins  = $time % 60;

						$timeString = sprintf('%02d:%02d', $hours, $mins);

						if ($timeString == $end[$c]) {
							echo '<option value="2|' . $start[$c] . '|' . $timeString . '|' . $id[$c] . '|' . $i . '" selected>' . $timeString . '</option>';
						}
						else {
							echo '<option value="2|' . $start[$c] . '|' . $timeString . '|' . $id[$c] . '|' . $i . '">' . $timeString . '</option>';
						}

						$time += 15;
					}

					echo '</select>
					</div>

					<div class="remove">
					<button type="button" class="remove-btn" onclick="this.form.changed.value=\'2|' . $id[$c] . '\'; this.form.submit()">✕</button>
					</div>		
					</div>';
				}
				echo '</form>';

			}

			?>

			<div class="inputform">
				<a href="mechanic-work-hours-restrictions.php" class="submit-button">Peržiūrėti mano darbo valandų apribojimus</a>
			</div>

	</div>

</body>
</html>