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

	if ($operation == "r") {
		$faq_id = htmlspecialchars($_POST['faq_id'] ?? '');

	    $stmt = $dbc->prepare("SELECT id FROM FAQ WHERE id = ?");
	    $stmt->bind_param("i", $faq_id);
	    $stmt->execute();
	    $check = $stmt->get_result();

	    if ($check->num_rows == 1) {
			$stmt = $dbc->prepare("DELETE FROM FAQ WHERE id = ?");
			$stmt->bind_param("i", $faq_id);
			$stmt->execute();
			$message = "Sėkmingai ištrynėte elementą.";
	    }
	}
	else if ($operation == "e") {
		$faq_id = htmlspecialchars($_POST['faq_id'] ?? '');

	    $stmt = $dbc->prepare("SELECT id FROM FAQ WHERE id = ?");
	    $stmt->bind_param("i", $faq_id);
	    $stmt->execute();
	    $check = $stmt->get_result();

		if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3 && $check->num_rows == 1) {
			$_SESSION['edit_faq_id'] = $faq_id;
			header("Location: edit-faq.php");
			exit();
		}
	}
	else if ($operation == "a") {
		if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3) {
			header("Location: add-faq.php");
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

	<?php
	$query = "SELECT * FROM FAQ";
	$result = mysqli_query($dbc, $query);
	$count = mysqli_num_rows($result);

	if ($count == 0) {
		echo '<div class="inputform">

		<div class="no-values">Nėra sukurta nė vieno DUK.</div>

		</div>';
	}

	while ($row = mysqli_fetch_assoc($result)) {
		echo '<div class="inputform">
		<div class="text">' . $row['question'] . '</div>
		<div class="comment">' . $row['answer'] .'</div>';


		if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] == 3)) {
			echo '<form method="post" style="margin-bottom: -10px">
			<button type="submit" class="submit-button" style="width: 100%"">Redaguoti</button>
			<input type="hidden" name="faq_id" value="' . $row['id'] .'">
			<input type="hidden" name="operation" value="e">
			</form>';

			echo '<form method="post">
			<button type="submit" class="remove-button" style="width: 100%">Ištrinti</button>
			<input type="hidden" name="faq_id" value="' . $row['id'] .'">
			<input type="hidden" name="operation" value="r">
			</form>';
		}
		echo '</div>';
	}

	if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] == 3)) {
		echo '<div class="inputform">
		<form method="post">
		<button type="submit" class="add-button" style="width: 100%">Pridėti</button>
		<input type="hidden" name="operation" value="a">
		</form>
		</div>';
	}
	?>
</div>

</body>
</html>

