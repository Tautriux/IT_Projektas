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

$form_submitted = false;
$mechanic_id = $_SESSION['user_id'];
$message = "";
$duration = $_POST['duration'] ?? "";
$service_id = $_POST['job'] ?? "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!filter_var($service_id, FILTER_VALIDATE_INT) || $service_id <= 0) {
        $message = "Netinkamas darbas.";
    }
    elseif (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $duration)) {
    	$message = "Netinkamas laikas.";
    }
    else {
	    list($h, $m) = explode(":", $duration);
	    $duration_in_minutes = ($h * 60) + $m;

	    $stmt = $dbc->prepare("
		    SELECT SERVICE.id
		    FROM SERVICE
		    LEFT JOIN (
		        SELECT * FROM SERVICE_COMPLETION 
		        WHERE fk_mechanic = ? AND removed = 0
		    ) AS SCM ON SERVICE.id = SCM.fk_service
		    WHERE SCM.fk_service IS NULL AND SERVICE.id = ?
		");
	    $stmt->bind_param("ii", $mechanic_id, $service_id);
	    $stmt->execute();
	    $result = $stmt->get_result();

	    if (!($result->num_rows == 0 || $duration_in_minutes <= 0 || $duration_in_minutes > 1425 || $m % 15 != 0)) {
		   	$stmt = $dbc->prepare("
		        INSERT INTO SERVICE_COMPLETION
		        (duration_in_minutes, fk_service, fk_mechanic, usedInRegistration, removed)
		        VALUES (?, ?, ?, 0, 0)
		    ");

		    $stmt->bind_param("iii", $duration_in_minutes, $service_id, $mechanic_id);
		    $stmt->execute();
		    $form_submitted = true;
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
		<?php if (!$form_submitted): ?>
			<?php if (!empty($message)): ?>
				<p class="error-message"><?php echo $message; ?></p>
			<?php endif; ?>

			<form method='post' class="inputform">
				<div class="input">
					<label for="job">Darbas</label>
					<select name="job" id="job">
						<option value="" disabled <?= $service_id === "" ? 'selected' : '' ?>>Pasirinkite darbą iš sąrašo</option>

						<?php 
						$query = "SELECT SERVICE.id as id, SERVICE.name as name FROM SERVICE LEFT JOIN (SELECT * FROM SERVICE_COMPLETION WHERE fk_mechanic = '$mechanic_id' AND removed = 0) AS SERVICE_COMPLETION_MECHANIC ON SERVICE.id = SERVICE_COMPLETION_MECHANIC.fk_service WHERE fk_service IS NULL";
						    $result = mysqli_query($dbc, $query);

						    if (mysqli_num_rows($result) > 0)  {
						        while ($row = mysqli_fetch_assoc($result)) {
						            $selected = ($service_id == $row['id']) ? 'selected' : '';
						            echo '<option value="'. $row['id'] .'" '. $selected .'>' . $row['name'] . '</option>';
						        }
						    }
						    else {
						        echo '<option disabled>Visi darbų trukmės jau nustatytos</option>';
						    }

						?>

					</select>
				</div>

				<div class="input">
					<label for="duration">Trukmė</label>	
					<select name="duration" id="duration">
					    <option value="" disabled <?= $duration === "" ? 'selected' : '' ?>>Pasirinkite darbo trukmę iš sąrašo</option>

					    <?php
					    for ($h = 0; $h < 24; $h++) {
					        for ($m = 0; $m < 60; $m += 15) {
					        	if ($h == 0 && $m == 0) {
					        		continue;
					        	}

					            $time = sprintf('%02d:%02d', $h, $m);
					            $selected = ($duration == $time) ? 'selected' : '';
					            echo '<option value="'. $time .'" '. $selected .'>' . $time . '</option>';
					        }
					    }
					    ?>
					</select>
				</div>
				<input type='submit' name='create' value='Pridėti' class="submit-button">
			</form>
		<?php else: ?>
			<div class="inputform">
				<div class="success-message-page">Elementas sėkmingai sukurtas!</div>
				<a href="jobs.php" class="submit-button">Grįžti į darbų trukmių puslapį</a>
			</div>
		<?php endif; ?>
	</div>

</body>
</html>