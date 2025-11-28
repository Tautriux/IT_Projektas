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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$operation = htmlspecialchars($_POST['operation']);

	if ($operation == "a") {
		header("Location: add-job.php");
		exit();
	}
	else if ($operation == "r") {
		$service_completion_id = htmlspecialchars($_POST['service_completion_id']);

		$stmt = $dbc->prepare("
		    SELECT usedInRegistration 
		    FROM SERVICE_COMPLETION 
		    WHERE id = ?
		");

		$stmt->bind_param("i", $service_completion_id);
		$stmt->execute();

		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		if ($row) {
			$used = $row['usedInRegistration'];
			if ($used == 0) {
			    $stmt = $dbc->prepare("
			        DELETE FROM SERVICE_COMPLETION
			        WHERE id = ?
			    ");
			    $stmt->bind_param("i", $service_completion_id);
			} elseif ($used == 1) {
			    $stmt = $dbc->prepare("
			        UPDATE SERVICE_COMPLETION
			        SET removed = 1
			        WHERE id = ?
			    ");
			    $stmt->bind_param("i", $service_completion_id);
			}

			$stmt->execute();

			$message = "Sėkmingai pašalintas darbas!";
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

		<div class="inputform" style="max-width: 600px !important;">
			<?php 
			$query = "SELECT SERVICE_COMPLETION.id as id, name, description, CONCAT( LPAD(FLOOR(duration_in_minutes / 60), 2, '0'), ':', LPAD(duration_in_minutes % 60, 2, '0')) AS duration FROM SERVICE INNER JOIN SERVICE_COMPLETION ON SERVICE.id = SERVICE_COMPLETION.fk_service WHERE fk_mechanic = '$mechanic_id' AND removed = 0";
			$result = mysqli_query($dbc, $query);
			$count = mysqli_num_rows($result);

			if ($count == 0) {
				echo '<div class="no-values">Neturite prisidėję jokių darbų.</div>';
			}
			else {
				echo '<table>';
				echo '
				<tr>
				<th>Darbas</th>
				<th>Darbo aprašymas</th>
				<th>Trukmė</th>
				<th>Šalinimas</th>
				</tr>';

				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr>' .
					'<td>' . $row['name'] . '</td>' .
					'<td>' . $row['description'] . '</td>' .
					'<td>' . $row['duration'] . '</td>


					<form method="post">
					<td>
					<button type="submit" class="remove-button" style="width: 100%">Šalinti</button>
					<input type="hidden" name="service_completion_id" value="' . $row['id'] . '">
					<input type="hidden" name="operation" value="r">
					</form>
					</td>

					</tr>';
				}
				echo '</table>';
			}

			echo '<form method="post">
			<input type="hidden" name="operation" value="a">
			<button type="submit" class="add-button" style="width: 100%">Pridėti</button>
			</form>';

			?>
		</div>
	</div>

</body>
</html>


