<?php
$usersFile = 'data/users.xml';

function updatePassword($file, $username, $hashedPassword) {
    $xml = simplexml_load_file($file);
    foreach ($xml->user as $user) {
        if ((string)$user->username === $username) {
            $user->password = $hashedPassword;
            $xml->asXML($file);
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $recoveryCode = $_POST['recoveryCode'];
    $newPassword = $_POST['newPassword'];

    $users = simplexml_load_file($usersFile);
    $userFound = false;
    foreach ($users->user as $user) {
        if ((string)$user->username === $username && (string)$user->recoveryCode === $recoveryCode) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            updatePassword($usersFile, $username, $hashedPassword);
            $userFound = true;
            break;
        }
    }

    if ($userFound) {
        $success = "Password has been reset successfully.";
    } else {
        $error = "Invalid username or recovery code.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
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
        <h1>Forgot Password</h1>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="post" action="forgot_password.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="text" name="recoveryCode" placeholder="Recovery Code" required><br>
            <input type="password" name="newPassword" placeholder="New Password" required><br>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
