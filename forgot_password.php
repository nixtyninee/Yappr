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
        $success = "your password has been reset, you can now login";
    } else {
        $error = "incorrect username or recovery code";
    }
}
?>
<!DOCTYPE html>
<html>
<?php require 'header.php'; ?>
<div class="container">

<div class="content">
        <h3>account recovery</h3>
        <?php if (isset($error)): ?>
            <?php echo $error; ?>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <?php echo $success; ?>
        <?php endif; ?>
        <form method="post" action="forgot_password.php">
            <div class="input-prepend">
                <span class="add-on">@</span>
                <input class="medium" name="username" size="20" type="text" placeholder="username" required/>
                <input class="medium" name="recoveryCode" type="text" placeholder="recovery code" required/>
                <input class="medium" name="newPassword" type="password" placeholder="new password" required/>
                <button type="submit" class="btn primary">reset</button>
        </form>
    </div>
    </div>
</body>
</html>
