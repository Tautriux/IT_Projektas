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
$form_submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$operation = htmlspecialchars($_POST['operation']);
	$mechanic_id = htmlspecialchars($_POST['mechanic_id']);

	if ($operation == "b") {
		$_SESSION['limit_mechanic_id'] = $mechanic_id;
		header("Location: work-hours-restrictions.php");
		exit();
	}
	else if ($operation == "a") {
		$week_day = htmlspecialchars($_POST['week_day'] ?? '');
		$start_time = htmlspecialchars($_POST['start_time'] ?? '');
		$end_time = htmlspecialchars($_POST['end_time'] ?? '');

		$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

		if ($week_day < 1 || $week_day > 7) {
			$message = "Netinkama diena.";
		}
		elseif (!preg_match($timePattern, $start_time)) {
			$message = "Netinkamas pradžios laikas.";
		}
		elseif (!preg_match($timePattern, $end_time)) {
			$message = "Netinkamas pabaigos laikas.";
		}
		else {
			list($start_hours, $start_minutes) = explode(":", $start_time);
			list($end_hours, $end_minutes) = explode(":", $end_time);

			if ($start_minutes % 15 == 0 && $end_minutes % 15 == 0) {
				if (strtotime($start_time) > strtotime($end_time)) {
					$message = "Pradžios laikas turi būti mažesnis už pabaigos laiką!";
				}
				else if (strtotime($start_time) == strtotime($end_time)) {
					$message = "Pradžios laikas negali būti lygus pabaigos laikui!";
				} else {
					$stmt = $dbc->prepare("
					    SELECT *
					    FROM WORK_HOURS_RESTRICTION
					    WHERE week_day = ?
					      AND fk_mechanic = ?
					      AND ? > start_time
					      AND ? < end_time
					");

					$stmt->bind_param("iiss", $week_day, $mechanic_id, $end_time, $start_time);
					$stmt->execute();

					$result = $stmt->get_result();
					$count = $result->num_rows;

					if ($count == 0) {
						$stmt = $dbc->prepare("
						    INSERT INTO WORK_HOURS_RESTRICTION (week_day, start_time, end_time, fk_mechanic)
						    VALUES (?, ?, ?, ?)
						");

						$stmt->bind_param("issi", $week_day, $start_time, $end_time, $mechanic_id);
						$stmt->execute();

						$stmt = $dbc->prepare("
						    SELECT *
						    FROM WORK_HOURS
						    WHERE week_day = ?
						      AND fk_mechanic = ?
						      AND ? > start_time
						      AND ? < end_time
						");

						$stmt->bind_param("iiss", $week_day, $mechanic_id, $end_time, $start_time);
						$stmt->execute();
						$overlaps = $stmt->get_result();

						while ($row = $overlaps->fetch_assoc()) {
							$start = $row['start_time'];
							$end = $row['end_time'];
							$id = $row['id'];

							if ($start >= $start_time && $end <= $end_time) {
								$stmt = $dbc->prepare("
								    DELETE FROM WORK_HOURS
								    WHERE id = ?
								");
								$stmt->bind_param("i", $id);
								$stmt->execute();
						    }
						    else if ($start < $start_time && $end > $start_time && $end <= $end_time) {
								$stmt = $dbc->prepare("
								    UPDATE WORK_HOURS
								    SET end_time = ?
								    WHERE id = ?
								");

								$stmt->bind_param("si", $start_time, $id);
								$stmt->execute();
						    }
						    else if ($start >= $start_time && $start < $end_time && $end > $end_time) {
								$stmt = $dbc->prepare("
								    UPDATE WORK_HOURS
								    SET start_time = ?
								    WHERE id = ?
								");

								$stmt->bind_param("si", $end_time, $id);
								$stmt->execute();
						    }
						    else if ($start < $start_time && $end > $end_time) {
								$stmt = $dbc->prepare("
								    UPDATE WORK_HOURS
								    SET end_time = ?
								    WHERE id = ?
								");

								$stmt->bind_param("si", $start_time, $id);
								$stmt->execute();

								$stmt = $dbc->prepare("
								    INSERT INTO WORK_HOURS (week_day, start_time, end_time, fk_mechanic)
								    VALUES (?, ?, ?, ?)
								");

								$stmt->bind_param("issi", $week_day, $end_time, $end, $mechanic_id);
								$stmt->execute();

						    }
						}
						$form_submitted = true;
					}
					else {
						$message = "Šis intervalas kertasi jau su egzistuojančiu apribojimo intervalu!";
					}
				}
			}
		}
		$_SESSION['add_limit_mechanic_id'] = $mechanic_id;
	}
}

if (!isset($_SESSION['add_limit_mechanic_id'])) {
	header("Location: index.php");
	exit();
}

$mechanic_id = $_SESSION['add_limit_mechanic_id'];
unset($_SESSION['add_limit_mechanic_id']);



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
			<p class="error-message"><?php echo $message; ?></p>
		<?php endif; ?>

		<?php if (!$form_submitted): ?>
			<form method='post' class="inputform">
				<input type="hidden" name="operation" value="a">
				<?php echo '<input type="hidden" name="mechanic_id" value="' . $mechanic_id .'">'; ?>

				<div class="input">
					<label for="week_day">Savaitės diena</label>
					<select name="week_day" id="week_day">
						<option value="" disabled <?php echo empty($_POST['week_day']) ? 'selected' : ''; ?>>Pasirinkite savaitės dieną iš sąrašo</option>
						<option value="1" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 1) ? 'selected' : ''; ?>>Pirmadienis</option>
						<option value="2" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 2) ? 'selected' : ''; ?>>Antradienis</option>
						<option value="3" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 3) ? 'selected' : ''; ?>>Trečiadienis</option>
						<option value="4" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 4) ? 'selected' : ''; ?>>Ketvirtadienis</option>
						<option value="5" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 5) ? 'selected' : ''; ?>>Penktadienis</option>
						<option value="6" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 6) ? 'selected' : ''; ?>>Šeštadienis</option>
						<option value="7" <?php echo (isset($_POST['week_day']) && $_POST['week_day'] == 7) ? 'selected' : ''; ?>>Sekmadienis</option>
					</select>
				</div>

				<div class="input">
					<label for="start_time">Pradžios laikas</label>
					<select name="start_time" id="start_time">
						<option value="" disabled <?php echo empty($_POST['start_time']) ? 'selected' : ''; ?>>Pasirinkite pradžios laiką iš sąrašo</option>
						<?php
						for ($h = 0; $h < 24; $h++) {
						    for ($m = 0; $m < 60; $m += 15) {
						        $time = sprintf("%02d:%02d", $h, $m);
						        echo '<option value = ' . $time;

						        if (isset($_POST['start_time']) && $_POST['start_time'] == $time) {
						        	echo ' selected';
						        }

						        echo '>' . $time . '</option>';
						    }
						}
						?>
					</select>
				</div>

				<div class="input">
					<label for="end_time">Pabaigos laikas</label>
					<select name="end_time" id="end_time">
						<option value="" disabled <?php echo empty($_POST['end_time']) ? 'selected' : ''; ?>>Pasirinkite pabaigos laiką iš sąrašo</option>
						<?php
						for ($h = 0; $h < 24; $h++) {
						    for ($m = 0; $m < 60; $m += 15) {
						    	$time = sprintf("%02d:%02d", $h, $m);
						        echo '<option value = ' . $time;

						        if (isset($_POST['end_time']) && $_POST['end_time'] == $time) {
						        	echo ' selected';
						        }

						        echo '>' . $time . '</option>';
						    }
						}
						?>
					</select>
				</div>



				<input type='submit' name='add' value='Pridėti' class="submit-button">
			</form>
		<?php else: ?>
			<form method='post' class="inputform">
				<div class="success-message-page">Sėkmingai pridėtas apribojimas!</div>
				<input type="hidden" name="operation" value="b">
				<?php echo '<input type="hidden" name="mechanic_id" value="' . $mechanic_id .'">'; ?>
				<button type="submit" class="submit-button">Grįžti į mechaniko apribojimų puslapį</button>
			</form>
		<?php endif; ?>
	</div>

</body>
</html>

