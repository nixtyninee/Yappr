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
        $error = "incorrect username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<?php require 'header.php'; ?>
<div class="container">

<div class="content">
        <h3>welcome back</h3>
        <?php if (isset($error)): ?>
            <?php echo $error; ?>
        <?php endif; ?>
        <form method="post" action="login.php">
            <div class="input-prepend">
                <span class="add-on">@</span>
                <input class="medium" name="username" size="20" type="text" placeholder="username" required/>
                <input class="medium" name="password" type="password" placeholder="password" required/>
                <button type="submit" class="btn primary">login</button>
                <a href="forgot_password.php" class="btn">reset password</a>
        </form>
    </div>
    </div>
</body>
</html>
