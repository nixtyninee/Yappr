<?php
session_start();

function loadPosts($file) {
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        return $xml->post;
    } else {
        return [];
    }
}

function savePost($file, $content) {
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
    } else {
        $xml = new SimpleXMLElement('<posts></posts>');
    }
    $id = 1;
    foreach ($xml->post as $post) {
        if (intval($post['id']) >= $id) {
            $id = intval($post['id']) + 1;
        }
    }
    $newPost = $xml->addChild('post');
    $newPost->addAttribute('id', $id);
    $newPost->addChild('author', $_SESSION['username']);
    $newPost->addChild('content', htmlspecialchars($content));
    $newPost->addChild('time', date('Y-m-d H:i:s'));
    $newPost->addChild('likes', 0);
    $newPost->addChild('likedBy');
    $xml->asXML($file);
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
                $dom = dom_import_simplexml($likedBy);
                $domNew = dom_import_simplexml($newLikedBy);
                foreach ($dom->childNodes as $child) {
                    $dom->removeChild($child);
                }
                foreach ($domNew->childNodes as $child) {
                    $dom->appendChild($dom->ownerDocument->importNode($child, true));
                }
            }
            $xml->asXML($file);
            return;
        }
    }
}

function deletePost($file, $postId) {
    $xml = simplexml_load_file($file);
    $dom = dom_import_simplexml($xml);
    foreach ($xml->post as $index => $post) {
        if (intval($post['id']) == intval($postId)) {
            unset($xml->post[$index]);
            $xml->asXML($file);
            return;
        }
    }
}

function deleteUser($file, $username) {
    $xml = simplexml_load_file($file);
    foreach ($xml->user as $index => $user) {
        if ((string)$user->username == $username) {
            unset($xml->user[$index]);
            $xml->asXML($file);
            return;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['content']) && isset($_SESSION['loggedin'])) {
        $content = $_POST['content'];
        savePost('data/posts.xml', $content);
        header('Location: index.php');
        exit();
    }
}

if (isset($_GET['action']) && isset($_GET['post']) && isset($_SESSION['loggedin'])) {
    $postId = $_GET['post'];
    $action = $_GET['action'];
    if ($action == 'delete' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'true') {
        deletePost('data/posts.xml', $postId);
    } else {
        updatePostLikes('data/posts.xml', $postId, $action);
    }
    header('Location: index.php');
    exit();
}

if (isset($_GET['deleteUser']) && isset($_SESSION['loggedin']) && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'true') {
    $usernameToDelete = $_GET['deleteUser'];
    deleteUser('data/users.xml', $usernameToDelete);
    header('Location: index.php');
    exit();
}

$posts = loadPosts('data/posts.xml');

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
    <title>Social Network</title>
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
        <?php if (isset($_SESSION['loggedin'])): ?>
            <form method="post" action="index.php">
                <textarea name="content" required></textarea><br>
                <button type="submit">Post</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Log in</a> or <a href="register.php">Register</a> to create a post.</p>
        <?php endif; ?>

        <h1>Latest Posts</h1>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <h3><a href="user.php?username=<?php echo $post->author; ?>"><?php echo $post->author; ?></a></h3>
                <p><?php echo $post->content; ?></p>
                <small><?php echo $post->time; ?> 
                <?php if (isset($_SESSION['loggedin'])): ?>
                    <?php if (userLikedPost($post->likedBy)): ?>
                        | <a href="index.php?action=unlike&post=<?php echo $post['id']; ?>" class="like-link">Unlike</a>
                    <?php else: ?>
                        | <a href="index.php?action=like&post=<?php echo $post['id']; ?>" class="like-link">Like</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'true'): ?>
                        | <a href="index.php?action=delete&post=<?php echo $post['id']; ?>" class="delete-link">Delete</a>
                    <?php endif; ?>
                <?php endif; ?> 
                (<?php echo $post->likes; ?> likes)</small>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'true'): ?>
            <h2>Delete Users</h2>
            <?php
            $users = simplexml_load_file('data/users.xml');
            foreach ($users->user as $user):
                if ((string)$user->username !== $_SESSION['username']): ?>
                    <p>
                        <?php echo $user->username; ?>
                        <a href="index.php?deleteUser=<?php echo $user->username; ?>" class="delete-user-link">Delete User</a>
                    </p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
