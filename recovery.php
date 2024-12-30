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
<?php require 'header.php'; ?>
<div class="container">

<div class="content">
        <h3>recovery code</h3>
        your recovery code is <b><?php echo $recoveryCode; ?></b>
        <br>
        please save this code before exiting the page, if you lose this code you wont be able to reset your password if you forget it
        <br>
        <a href="index.php">continue</a>
    </div>
    </div>
</body>
</html>
