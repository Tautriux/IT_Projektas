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

$faq_id = htmlspecialchars($_POST['faq_id'] ?? '');
$question = htmlspecialchars($_POST['question'] ?? '');
$answer = htmlspecialchars($_POST['answer'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$stmt = $dbc->prepare("
	    SELECT *
	    FROM FAQ
	    WHERE id = ?
	");

	$stmt->bind_param("i", $faq_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$count = $result->num_rows;


	if (strlen($question) < 2 || strlen($question) > 255) {
        $message = "Klausimas turi būti nuo 2 iki 255 simbolių.";
    }
    elseif (strlen($answer) < 1 || strlen($answer) > 1000) {
        $message = "Atsakymas turi būti nuo 1 iki 1000 simbolių.";
    }
    elseif ($count == 1) {
		$stmt = $dbc->prepare("
		    UPDATE FAQ
		    SET question = ?, answer = ?
		    WHERE id = ?
		");

		$stmt->bind_param("ssi", $question, $answer, $faq_id);
		$stmt->execute();

    	$form_submitted = true;
    }

}
else if (!isset($_SESSION['edit_faq_id'])) {
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
				<?php
				if (isset($_SESSION['edit_faq_id'])) {
					$faq_id = $_SESSION['edit_faq_id'];
				}

			    $stmt = $dbc->prepare("
			        SELECT *
			        FROM FAQ
			        WHERE id = ?
			    ");

			    $stmt->bind_param("i", $faq_id);
			    $stmt->execute();
			    $result = $stmt->get_result();
			    $row = $result->fetch_assoc();

				?>

			    <?php if ($row): ?>
			    	<?php if (!empty($message)): ?>
						<p class="error-message"><?php echo $message; ?></p>
					<?php endif; ?>
			        <form method='post' class="inputform">

			            <input type="hidden" name="faq_id" value="<?= $faq_id ?>">

			            <div class="input">
			                <label for="question">Klausimas</label>
			                <input name="question" type="text" value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? $question : $row['question']) ?>">
			            </div>

			            <div class="input">
			                <label for="answer">Atsakymas</label>
			                <textarea name="answer" style="resize: none; height: 80px;"><?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? $answer : $row['answer']) ?></textarea>
			            </div>

			            <input type='submit' name='write' value='Atnaujinti' class="submit-button">
			        </form>

			    <?php else: ?>
			        <div class="inputform">
			            <p class="error-message">Toks DUK įrašas neegzistuoja.</p>
			            <a href="duk.php" class="submit-button">Grįžti į DUK puslapį</a>
			        </div>
			    <?php endif; ?>

			</form>
		<?php else: ?>
			<div class="inputform">
				<div class="success-message-page">Duomenys sėkmingai atnaujinti!</div>
				<a href="duk.php" class="submit-button">Grįžti į DUK puslapį</a>
			</div>
		<?php endif; ?>
	</div>

</body>
</html>

<?php 
unset($_SESSION['edit_faq_id']);
?>