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
<?php require 'header.php'; ?>
<div class="container">

<div class="content">
        <div class="row">
        <div class="span10">
        <h3>recent yaps</h3>
    <?php foreach ($posts as $post): ?>
        <div class="well" style="padding: 14px 19px;">
        <p><b><a href="user.php?username=<?php echo $post->author; ?>"><?php echo $post->author; ?></a></b> <?php echo $post->content; ?></p>
        <?php if (isset($_SESSION['loggedin'])) { ?>
            <?php if (userLikedPost($post->likedBy)) { ?>
                <a href="index.php?action=unlike&post=<?php echo $post['id']; ?>" class="btn small danger">▼ <?php echo $post->likes; ?></a>
            <?php } else { ?>
                <a href="index.php?action=like&post=<?php echo $post['id']; ?>" class="btn small success">▲ <?php echo $post->likes; ?></a>
            <?php }; ?>
        <?php }; ?> 
            </div>
    <?php endforeach; ?>
            </div>
            <div class="span4">
            <?php if (isset($_SESSION['loggedin'])): ?>
            <?php
            $phrases = ["welcome back", "what's up?", "what's going on?", "how have you been?", "what are you doing?"];
            $random_phrase = $phrases[array_rand($phrases)];
            ?>
            <h3><?php echo $random_phrase; ?></h3>
            <form method="post" action="index.php">
            <textarea class="xxsmall" id="textarea" name="content" required></textarea>
            <br>
            <button type="submit" class="btn primary">post</button>
        </form>
        <?php else: ?>
        <h3>welcome to yappr</h3>
        <a href="login.php" class="btn small">login</a> or <a href="register.php" class="btn small">create an account</a>
        <?php endif; ?>
        </div>
        </div>
            </div>
    </div>
</body>
</html>
