<?php
session_start();

$usersFile = 'data/users.xml';

function loadUsers($file) {
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        $users = [];
        foreach ($xml->user as $user) {
            $users[(string)$user->username] = (string)$user->password;
        }
        return $users;
    } else {
        return [];
    }
}

$users = loadUsers($usersFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>Social Network</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
        <a href="forgot_password.php">Forgot Password</a>
    </nav>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
