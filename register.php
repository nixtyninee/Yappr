<?php
session_start();

$usersFile = 'data/users.xml';

function saveUser($file, $username, $hashedPassword, $recoveryCode, $isAdmin) {
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
    } else {
        $xml = new SimpleXMLElement('<users></users>');
    }
    $user = $xml->addChild('user');
    $user->addChild('username', $username);
    $user->addChild('password', $hashedPassword);
    $user->addChild('recoveryCode', $recoveryCode);
    $user->addChild('isAdmin', $isAdmin);
    $xml->asXML($file);
}

function generateRecoveryCode() {
    return bin2hex(random_bytes(5)); // 10 characters long
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $recoveryCode = generateRecoveryCode();
    $isAdmin = isset($_POST['isAdmin']) ? 'true' : 'false';

    $users = simplexml_load_file($usersFile);
    $usernames = [];
    foreach ($users->user as $user) {
        $usernames[] = (string)$user->username;
    }

    if (in_array($username, $usernames)) {
        $error = "Username already exists. Please choose another one.";
    } else {
        saveUser($usersFile, $username, $hashedPassword, $recoveryCode, $isAdmin);
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['recoveryCode'] = $recoveryCode;
        $_SESSION['isAdmin'] = $isAdmin;
        header('Location: recovery.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>Social Network</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <label><input type="checkbox" name="isAdmin"> Admin</label><br>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
