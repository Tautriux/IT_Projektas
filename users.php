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

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 3 ) {
	header("Location: index.php");
	exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$user_id = htmlspecialchars($_POST['user_id']);
	$operation = htmlspecialchars($_POST['operation']);

	$stmt = $dbc->prepare(
		"SELECT * FROM SYSTEM_USER 
		 WHERE ? = id"
	);

	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$check_result = $stmt->get_result();

	if ($check_result->num_rows == 1) {
		if ($operation == "e") {
			$role = htmlspecialchars($_POST['role']);

			$stmt = $dbc->prepare("
		        UPDATE SYSTEM_USER
		        SET fk_role = ?
		        WHERE id = ?
		    ");
		    $stmt->bind_param("ii", $role, $user_id);
		    $stmt->execute();

			if ($role == 1) {
		        $tables = [
		            "REGISTRATION" => "fk_mechanic",
		            "REVIEW" => "fk_mechanic",
		            "SERVICE_COMPLETION" => "fk_mechanic",
		            "WORK_HOURS" => "fk_mechanic",
		            "WORK_HOURS_RESTRICTION" => "fk_mechanic"
		        ];
			}
			else {
		        $tables = [
		            "REGISTRATION" => "fk_client",
		            "REVIEW" => "fk_client"
		        ];		
			}

		    foreach ($tables as $table => $column) {
		        $stmt = $dbc->prepare("
		            DELETE FROM $table
		            WHERE $column = ?
		        ");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();
		    }
			
			$message = "Sėkmingai pakeista vartotojo rolė!";
		}
		else if ($operation == "b") {
			$stmt = $dbc->prepare("
			    UPDATE SYSTEM_USER
			    SET blocked = 1
			    WHERE id = ?
			");

			$stmt->bind_param("i", $user_id);
			$stmt->execute();
			$message = "Vartotojas sėkmingai užblokuotas!";
		}
		else if ($operation == "u") {
			$stmt = $dbc->prepare("
			    UPDATE SYSTEM_USER
			    SET blocked = 0
			    WHERE id = ?
			");

			$stmt->bind_param("i", $user_id);
			$stmt->execute();
			$message = "Vartotojas sėkmingai atblokuotas!";
		}
		else if ($operation == "r") {
		    $stmt = $dbc->prepare("SELECT fk_role FROM SYSTEM_USER WHERE id = ?");
		    $stmt->bind_param("i", $user_id);
		    $stmt->execute();
		    $result = $stmt->get_result();
		    $row = $result->fetch_assoc();
			$role = $row['fk_role'];

			if ($role == 2) {
		        $stmt = $dbc->prepare("DELETE FROM REGISTRATION WHERE fk_mechanic = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();

		        $stmt = $dbc->prepare("DELETE FROM REVIEW WHERE fk_mechanic = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();

		        $stmt = $dbc->prepare("DELETE FROM SERVICE_COMPLETION WHERE fk_mechanic = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();

		        $stmt = $dbc->prepare("DELETE FROM WORK_HOURS WHERE fk_mechanic = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();

		        $stmt = $dbc->prepare("DELETE FROM WORK_HOURS_RESTRICTION WHERE fk_mechanic = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();
			}
			else {
		        $stmt = $dbc->prepare("DELETE FROM REGISTRATION WHERE fk_client = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();

		        $stmt = $dbc->prepare("DELETE FROM REVIEW WHERE fk_client = ?");
		        $stmt->bind_param("i", $user_id);
		        $stmt->execute();		
			}

		    $stmt = $dbc->prepare("DELETE FROM SYSTEM_USER WHERE id = ?");
		    $stmt->bind_param("i", $user_id);
		    $stmt->execute();

			$message = "Vartotojas sėkmingai ištrintas!";
		}
		else if ($operation == "l") {
			$_SESSION['limit_mechanic_id'] = $user_id;
			header("Location: work-hours-restrictions.php");
			exit();
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

		<div class="inputform" style="max-width: 1800px !important;">
			<?php 
			$query = "SELECT id, username, name, surname, email, phone, fk_role, blocked FROM SYSTEM_USER WHERE fk_role != 3";
			$result = mysqli_query($dbc, $query);
			$count = mysqli_num_rows($result);

			if ($count == 0) {
				echo '<div class="no-values">Nėra jokių vartotojų sistemoje.</div>';
			}
			else {
				$rolesQuery = "SELECT * FROM USER_ROLE WHERE id != 3";
				$rolesResult = mysqli_query($dbc, $rolesQuery);
				$roles = mysqli_fetch_all($rolesResult, MYSQLI_ASSOC);

				echo '<table>';
				echo '
				<tr>
				<th>Slapyvardis</th>
				<th>Vardas</th>
				<th>Pavardė</th>
				<th>El. paštas</th>
				<th>Tel. numeris</th>
				<th>Rolė</th>
				<th>Blokavimas</th>
				<th>Šalinimas</th>
				<th>Darbo valandų apribojimai</th>
				</tr>';

				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr>' .
					'<td>' . $row['username'] . '</td>' .
					'<td>' . $row['name'] . '</td>' .
					'<td>' . $row['surname'] . '</td>' .
					'<td>' . $row['email'] . '</td>' .
					'<td>' . $row['phone'] . '</td>';

					echo '<form method="post" class="inputform">
					<input type="hidden" name="role" value="">
					<input type="hidden" name="user_id" value="' . $row['id'] .'">
					<input type="hidden" name="operation" value="e">

					<td>
					<div class="input">
					<select onchange="this.form.role.value=this.value; this.form.submit()" required>';

					foreach ($roles as $role) {

						echo '<option value="' . $role['id'] . '"';
						if ($role['id'] == $row['fk_role']) {
							echo ' selected';
						}
						echo '>' . $role['name'] . '</option>';
					}

					
					echo '</select>
					</div>
					</form>
					</td>';


					echo '<form method="post">
					<td>';

					if ($row['blocked'] == 0) {
						echo '<button type="submit" class="submit-button" style="width: 100%">Užblokuoti</button>
						<input type="hidden" name="user_id" value="' . $row['id'] .'">
						<input type="hidden" name="operation" value="b">';
					}
					else {
						echo '<button type="submit" class="submit-button" style="width: 100%">Atblokuoti</button>
						<input type="hidden" name="user_id" value="' . $row['id'] .'">
						<input type="hidden" name="operation" value="u">';
					}
					
					echo '
					</form>
					</td>';

					echo '<form method="post">
					<td>
					<button type="submit" class="remove-button" style="width: 100%">Šalinti</button>
					<input type="hidden" name="user_id" value="' . $row['id'] .'">
					<input type="hidden" name="operation" value="r">
					</form>
					</td>';


					if ($row['fk_role'] == 2) {
						echo '<form method="post">
						<td>
						<button type="submit" class="submit-button" style="width: 100%">Keisti</button>
						<input type="hidden" name="user_id" value="' . $row['id'] .'">
						<input type="hidden" name="operation" value="l">
						</form>
						</td>';
					}
					else {
						echo '<td></td>';
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

