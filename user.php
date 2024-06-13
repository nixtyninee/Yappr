<?php
session_start();

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

function loadPosts($file, $username) {
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        $posts = [];
        foreach ($xml->post as $post) {
            if ($post->author == $username) {
                $posts[] = $post;
            }
        }
        return $posts;
    } else {
        return [];
    }
}

function updatePostLikes($file, $postId, $action) {
    $xml = simplexml_load_file($file);
    foreach ($xml->post as $post) {
        if (intval($post['id']) == intval($postId)) {
            $likes = intval($post->likes);
            $likedBy = $post->likedBy;
            $username = $_SESSION['username'];

            $likedByArray = [];
            foreach ($likedBy->user as $user) {
                $likedByArray[] = (string)$user;
            }

            if ($action == 'like' && !in_array($username, $likedByArray)) {
                $post->likes = $likes + 1;
                $likedBy->addChild('user', $username);
            } elseif ($action == 'unlike' && in_array($username, $likedByArray)) {
                $post->likes = max(0, $likes - 1);
                $newLikedBy = new SimpleXMLElement('<likedBy></likedBy>');
                foreach ($likedByArray as $user) {
                    if ($user != $username) {
                        $newLikedBy->addChild('user', $user);
                    }
                }
                $post->likedBy = $newLikedBy;
            }
            $xml->asXML($file);
            return;
        }
    }
}

if (!isset($_GET['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_GET['username'];
$users = loadUsers('data/users.xml');

if (!isset($users[$username])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['action']) && isset($_GET['post']) && isset($_SESSION['loggedin'])) {
    $postId = $_GET['post'];
    $action = $_GET['action'];
    updatePostLikes('data/posts.xml', $postId, $action);
    header('Location: user.php?username=' . $username);
    exit();
}

$posts = loadPosts('data/posts.xml', $username);

function userLikedPost($likedBy) {
    if (isset($_SESSION['loggedin'])) {
        $username = $_SESSION['username'];
        foreach ($likedBy->user as $user) {
            if ($user == $username) {
                return true;
            }
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $username; ?>'s Profile</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>Social Network</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['loggedin'])): ?>
            <a href="user.php?username=<?php echo $_SESSION['username']; ?>">Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <h1><?php echo $username; ?>'s Profile</h1>
        <h2>Posts</h2>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><?php echo $post->content; ?></p>
                <small><?php echo $post->time; ?>
                <?php if (isset($_SESSION['loggedin'])): ?>
                    <?php if (userLikedPost($post->likedBy)): ?>
                        | <a href="user.php?username=<?php echo $username; ?>&action=unlike&post=<?php echo $post['id']; ?>" class="like-link">Unlike</a>
                    <?php else: ?>
                        | <a href="user.php?username=<?php echo $username; ?>&action=like&post=<?php echo $post['id']; ?>" class="like-link">Like</a>
                    <?php endif; ?>
                <?php endif; ?>
                (<?php echo $post->likes; ?> likes)</small>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
