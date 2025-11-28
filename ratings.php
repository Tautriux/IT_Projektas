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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mechanic_id = htmlspecialchars($_POST['mechanic_id']);
	$operation = htmlspecialchars($_POST['operation']);

	$stmt = $dbc->prepare("
		SELECT * 
		FROM SYSTEM_USER 
		WHERE id = ? AND fk_role = 2");

	$stmt->bind_param("i", $mechanic_id);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows == 1) {
		if ($operation == "r") {
			$_SESSION['reviews_mechanic_id'] = $mechanic_id;
			header("Location: mechanic-reviews.php");
			exit();
		}
		else if ($operation == "w") {
			if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
				$_SESSION['write_review_mechanic_id'] = $mechanic_id;
				header("Location: write-review.php");
				exit();
			}
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
		<div class="inputform">
			<?php 
			$query = "SELECT SYSTEM_USER.id as mechanic_id, CONCAT(name, ' ', surname) AS full_name, IFNULL((SELECT ROUND(AVG(rating), 1) FROM REVIEW WHERE fk_mechanic = SYSTEM_USER.id), 0) as average_rating FROM SYSTEM_USER WHERE fk_role = 2";
			$result = mysqli_query($dbc, $query);
			$count = mysqli_num_rows($result);

			if ($count == 0) {
				echo '<div class="no-values">Nėra jokių mechanikų sistemoje.</div>';
			}
			else {
				echo '<table>';
				echo '
				<tr>
				<th>Mechanikas</th>
				<th>Įvertinimas</th>
				<th>Skaityti atsiliepimus</th>';

				if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
					echo '<th>Rašyti atsiliepimą</th>';
				}

				echo '</tr>';

				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr>' .
					'<td>' . $row['full_name'] . '</td>' .
					'<td>' . $row['average_rating'] . '</td>';

					echo '<form method="post">
					<td>
					<button type="submit" class="submit-button" style="width: 100%">Skaityti</button>
					<input type="hidden" name="mechanic_id" value="' . $row['mechanic_id'] .'">
					<input type="hidden" name="operation" value="r">
					</form>
					</td>';

					
					if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
						echo '<form method="post">
						<td>
						<button type="submit" class="submit-button" style="width: 100%">Rašyti</button>
						<input type="hidden" name="mechanic_id" value="' . $row['mechanic_id'] .'">
						<input type="hidden" name="operation" value="w">
						</form>
						</td>';
					}

					echo '</tr>';
				}
				echo '</table>';
			}

			?>
		</div>
	</div>

</body>
</html>

