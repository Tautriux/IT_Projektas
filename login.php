<?php 
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$server = "localhost";
$db = "cars_service";
$user = "stud";
$password = "stud";

$dbc = mysqli_connect($server, $user, $password, $db);

if(!$dbc) { die ("Negaliu prisijungti prie duomenų bazės:".mysqli_error($dbc)); }

$message = "";

if($_POST != null) {
	$username = htmlspecialchars($_POST['username']);
	$password = htmlspecialchars($_POST['password']);

	$stmt = $dbc->prepare("SELECT * FROM SYSTEM_USER WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();

	$check_result = $stmt->get_result();
	$row = $check_result->fetch_assoc();

	if ($check_result->num_rows == 1 && $row['blocked'] == 0) {
		if (password_verify($password, $row['encrypted_password'])) {
			$_SESSION['user_id'] = $row['id'];
        	$_SESSION['user_role'] = $row['fk_role'];
        	$_SESSION['user_blocked'] = $row['blocked'];
        	header("Location: index.php");
        	exit();
		}
		else {
			$message = "Neteisingi prisijungimo duomenys!";
		}

	} else if ($check_result->num_rows == 1 && $row['blocked'] == 1) {
		$message = "Vartotojas yra užblokuotas!";
	} else {
		$message = "Neteisingi prisijungimo duomenys!";
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
			<a href="ratings.php">Atsiliepimai apie meistrus</a>
			<a href="duk.php">DUK</a>
		</nav>

		<div class="actions">
			<a href="login.php" class="login">Prisijungti</a>
			<a href="register.php" class="register">Registruotis</a>
		</div>

	</div>


	<div class="content">
		<?php if (!empty($message)): ?>
			<p class="error-message"><?php echo $message; ?></p>
		<?php endif; ?>
		<form method='post' class="inputform">
			<div class="input">
				<label for="username">Prisijungimo vardas</label>
				<input name="username" type="text"
				value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">	
			</div>

			<div class="input">
				<label for="password">Slaptažodis</label>
				<input name="password" type="password">
			</div>

			<input type='submit' name='login' value='Prisijungti' class="submit-button">
		</form>
	</div>


</body>
</html>