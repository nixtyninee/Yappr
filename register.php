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
    return bin2hex(random_bytes(5)); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $recoveryCode = generateRecoveryCode();
    $isAdmin = 'false';

    $users = simplexml_load_file($usersFile);
    $usernames = [];
    foreach ($users->user as $user) {
        $usernames[] = (string)$user->username;
    }

    if (in_array($username, $usernames)) {
        $error = "this username is already in use, please pick another one";
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
<?php require 'header.php'; ?>
<div class="container">

<div class="content">
        <h3>create an account</h3>
        <?php if (isset($error)): ?>
            <?php echo $error; ?>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="input-prepend">
                <span class="add-on">@</span>
                <input class="medium" name="username" size="20" type="text" placeholder="username" required/>
                <input class="medium" name="password" type="password" placeholder="password" required/>
                <button type="submit" class="btn primary">register</button>
        </form>
    </div>
    </div>
</body>
</html>
