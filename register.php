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

$form_submitted = false;
$message = "";

if($_POST != null) {
	$username = htmlspecialchars($_POST['username']);
	$password = htmlspecialchars($_POST['password']);
	$name = htmlspecialchars($_POST['name']);
	$surname = htmlspecialchars($_POST['surname']);
	$email = htmlspecialchars($_POST['email']);
	$phone = htmlspecialchars($_POST['phone']);
	$role = htmlspecialchars($_POST['role'] ?? '');

	if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
	    $message  = "Slapyvardžio ilgis turi būti nuo 3 iki 30 simbolių, jame gali būti tik raidės, skaičiai ir apatinis brūkšnys.";
	}
	elseif (!preg_match('/^.{8,60}$/', $password)) {
	    $message  = "Slaptažodžio ilgis turi būti nuo 8 iki 64 simbolių.";
	}
	elseif (!preg_match('/^[A-Za-zÀ-ž\s-]{2,30}$/', $name)) {
	    $message  = "Vardo ilgis turi būti nuo 2 iki 30 simbolių, jame gali būti tik raidės.";
	}
	elseif (!preg_match('/^[A-Za-zÀ-ž\s-]{2,30}$/', $surname)) {
	    $message  = "Pavardės ilgis turi būti nuo 2 iki 30 simbolių, joje gali būti tik raidės.";
	}
	elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
	    $message  = "El. paštas negali viršyti 100 simbolių ir turi būti tokiu formatu: paštas@domenas.com";
	}
	elseif (!preg_match('/^\+370[0-9]{8}$/', $phone)) {
	    $message  = "Telefono numeris turi būti formatu +370XXXXXXXX.";
	}
	elseif (!preg_match('/^(client|mechanic)$/', $role)) {
	    $message  = "Nepasirinkta tinkama rolė.";
	}
	else {
		$stmt = $dbc->prepare(
		    "SELECT * FROM SYSTEM_USER 
		     WHERE LOWER(username) = LOWER(?) 
		        OR LOWER(email) = LOWER(?)"
		);

		$stmt->bind_param("ss", $username, $email);
		$stmt->execute();

		$check_result = $stmt->get_result();

		if ($check_result->num_rows > 0) {
			$message = "Toks vartotojo vardas arba el. paštas jau egzistuoja.";
		} else {
			$encrypted_password = password_hash($password, PASSWORD_DEFAULT);
			$role_id = ($role === "client") ? 1 : 2;

			$stmt = $dbc->prepare("
			    INSERT INTO SYSTEM_USER 
			        (username, encrypted_password, name, surname, email, phone, fk_role)
			    VALUES (?, ?, ?, ?, ?, ?, ?)
			");

			$stmt->bind_param(
			    "ssssssi", 
			    $username, 
			    $encrypted_password, 
			    $name, 
			    $surname, 
			    $email, 
			    $phone, 
			    $role_id
			);

			if ($stmt->execute()) {
			    $form_submitted = true;
			} else {
			    $message = "Klaida registruojantis: " . $stmt->error;
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

		<?php if (!$form_submitted): ?>
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

				<div class="input">
					<label for="name">Vardas</label>
					<input name="name" type="text"
					value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
				</div>

				<div class="input">
					<label for="surname">Pavardė</label>
					<input name="surname" type="text"
					value="<?php echo isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : ''; ?>">
				</div>

				<div class="input">
					<label for="email">El. paštas</label>
					<input name="email" type="text"
					value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
				</div>

				<div class="input">
					<label for="phone">Telefono numeris</label>
					<input name="phone" type="text"
					value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
				</div>

				<div class="input">
					<label for="role">Rolė</label>
					<select name="role" id="role">
						<option value="" disabled <?php echo empty($_POST['role']) ? 'selected' : ''; ?>>Pasirinkite rolę</option>
						<option value="client" <?php echo (isset($_POST['role']) && $_POST['role'] == 'client') ? 'selected' : ''; ?>>Klientas</option>
						<option value="mechanic" <?php echo (isset($_POST['role']) && $_POST['role'] == 'mechanic') ? 'selected' : ''; ?>>Meistras</option>
					</select>
				</div>

				<input type='submit' name='register' value='Registruotis' class="submit-button">
			</form>


		<?php else: ?>
			<div class="inputform">
				<div class="success-message-page">Registracija sėkminga!</div>
				<a href="login.php" class="submit-button">Prisijungti</a>
			</div>
		<?php endif; ?>

	</div>


</body>
</html>
