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

$form_submitted = false;
$message = "";
$rating = '';
$comment = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$client_id = $_SESSION['user_id'];
	$mechanic_id = htmlspecialchars($_POST['mechanic_id'] ?? '');
	$rating = htmlspecialchars($_POST['rating'] ?? '');
	$comment = htmlspecialchars($_POST['comment'] ?? '');

	if (!preg_match('/^[1-5]$/', $rating)) {
        $message = "Neteisingas įvertinimas.";
    }
    elseif (strlen($comment) < 1 || strlen($comment) > 1000) {
        $message = "Komentaro ilgis turi būti 1–1000 simbolių.";
    }
    else {
		$stmt = $dbc->prepare("
			SELECT * 
			FROM SYSTEM_USER 
			WHERE id = ? AND fk_role = 2");

		$stmt->bind_param("i", $mechanic_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows == 1) {
			$stmt = $dbc->prepare("
		    INSERT INTO REVIEW (rating, comment, date_time, fk_client, fk_mechanic)
		    VALUES (?, ?, NOW(), ?, ?)
		    ");

			$stmt->bind_param("isii", $rating, $comment, $client_id, $mechanic_id);
			$stmt->execute();
			$form_submitted = true;
			unset($_SESSION['write_review_mechanic_id']);
		}
		
    }
}
else if (!isset($_SESSION['write_review_mechanic_id'])) {
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
		<?php if (!$form_submitted): ?>
			<?php if (!empty($message)): ?>
				<p class="error-message"><?php echo $message; ?></p>
			<?php endif; ?>

			<form method='post' class="inputform">

			    <?php if (isset($_SESSION['write_review_mechanic_id'])): ?>
			        <input type="hidden" name="mechanic_id" value="<?= $_SESSION['write_review_mechanic_id'] ?>">
			    <?php endif; ?>

			    <div class="input">
			        <label for="rating">Įvertinimas</label>
			        <select name="rating" id="rating">
			            <option value="" disabled <?= $rating === '' ? 'selected' : '' ?>>Pasirinkite įvertinimą</option>
			            <option value="1" <?= $rating == '1' ? 'selected' : '' ?>>1</option>
			            <option value="2" <?= $rating == '2' ? 'selected' : '' ?>>2</option>
			            <option value="3" <?= $rating == '3' ? 'selected' : '' ?>>3</option>
			            <option value="4" <?= $rating == '4' ? 'selected' : '' ?>>4</option>
			            <option value="5" <?= $rating == '5' ? 'selected' : '' ?>>5</option>
			        </select>
			    </div>

			    <div class="input">
			        <label for="comment">Komentaras</label>
			        <textarea name="comment" style="resize: none; height: 100px;"><?= $comment ?></textarea>
			    </div>

			    <input type='submit' name='write' value='Įrašyti' class="submit-button">
			</form>

		<?php else: ?>
			<div class="inputform">
				<div class="success-message-page">Atsiliepimas pateiktas sėkmingai!</div>
				<a href="ratings.php" class="submit-button">Grįžti į mechanikų sąrašą</a>
			</div>
		<?php endif; ?>
	</div>

</body>
</html>