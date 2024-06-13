<?php
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['recoveryCode'])) {
    header('Location: index.php');
    exit();
}

$recoveryCode = $_SESSION['recoveryCode'];
unset($_SESSION['recoveryCode']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recovery Code</title>
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
        <h1>Account Recovery</h1>
        <p>Your recovery code is: <strong><?php echo $recoveryCode; ?></strong></p>
        <p>Please save this code in a secure place. You will need it to reset your password if you forget it.</p>
        <p><a href="index.php">Go to Home</a></p>
    </div>
</body>
</html>
