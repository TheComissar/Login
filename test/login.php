<?php

require_once './includes/init.php';

use Foundationphp\Sessions\AutoLogin;

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $pwd = trim($_POST['pwd']);
    $stmt = $db->prepare('SELECT pwd FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $stored = $stmt->fetchColumn();
    if (password_verify($pwd, $stored)) {
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['authenticated'] = true;
        if (isset($_POST['remember'])) {
            // create persistent login
            $autologin = new AutoLogin($db);
            $autologin->persistentLogin();
        }
        header('Location: restricted1.php');
        exit;
    } else {
        $error = 'Login failed. Check username and password.';
    }
}
if (isset($_POST['fpass'])) {
    if ($_POST['username'] != '') {
        $error='Username not found.';
        $username = trim($_POST['username']);
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->fetchColumn() != 0) {
            $error='Email sent!';
            $stmt = $db->prepare('SELECT email FROM users WHERE username = :username');
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $stored = $stmt->fetchColumn();
            mail("EStrohbusch@etsexpress.com", 'Your Password', $stored, '');
        }
    } else {
        $error = 'Password Reset failed. Please input Username.';
    }


}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Auto Login</title>
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
<h1>Persistent Login</h1>
<?php
if (isset($error)) {
    echo "<p>$error</p>";
}
?>
<form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
    <p>
        <label for="username">Username</label><br>
        <input type="text" name="username" id="username">
    </p>
    <p>
        <label for="password">Password</label><br>
        <input type="password" name="pwd" id="pwd">
    </p>
    <div class="g-recaptcha" data-sitekey="6LdxvRoUAAAAANHjjZi7zZT-zMSTph9nGXcbk8gS"></div>
    <p>
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Remember me </label>
    </p>
    <p>
        <input style="float: left;"type="submit" name="login" id="login" value="Log In">
    </p>
    <p>
        <input style="float: left;" type="submit" name="fpass" id="fpass" value="Forgot Password">
    </p>

</form>
</body>
</html>