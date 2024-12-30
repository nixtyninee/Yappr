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
<?php require 'header.php'; ?>
    <div class="container">

    <div class="content">

        <h3><small>@</small><?php echo $username; ?></h3>
    <?php foreach ($posts as $post): ?>
        <div class="well" style="padding: 14px 19px;">
        <p><b><a href="user.php?username=<?php echo $post->author; ?>"><?php echo $post->author; ?></a></b> <?php echo $post->content; ?></p>
        <?php if (isset($_SESSION['loggedin'])) { ?>
            <?php if (userLikedPost($post->likedBy)) { ?>
                <a href="user.php?username=<?php echo $username; ?>&action=unlike&post=<?php echo $post['id']; ?>" class="btn small danger">▼ <?php echo $post->likes; ?></a>
            <?php } else { ?>
                <a href="user.php?username=<?php echo $username; ?>&action=like&post=<?php echo $post['id']; ?>" class="btn small success">▲ <?php echo $post->likes; ?></a>
            <?php }; ?>
        <?php }; ?> 
            </div>
    <?php endforeach; ?>
    </div>
            </div>
</body>
</html>
