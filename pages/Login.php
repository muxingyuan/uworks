<?php
// lifetime: 1 day (24 hours/day, 60 minutes/hour, and 60 seconds/minute)
session_start(['cookie_lifetime' => 24*60*60,]) or die("Cannot start the session. Are cookies enabled?");
if (!isset($_SESSION["token"])) {
    $_SESSION["token"] = bin2hex(random_bytes(24));
}

require_once "../common/functions_defs.php";
$title = "Login";
$username = $username_error = $password = "";
$password_error = $login_error = $token_error = $db_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $token_error = "Error: cannot process the form";
    }
    if (empty(trim($_POST["username"]))) {
        $username_error = "Error: no user name provided";
    }
    if (empty(trim($_POST["password"]))) {
        $password_error = "Error: no password provided";
    }
    if ($username_error == "" && $password_error == "" && $token_error == "") {
        $username = clean_data($_POST["username"]);
        $password = clean_data($_POST["password"]);

        $conn = get_introdb_conn();
        if ($conn->connect_error) {
            $db_error = "Connection failed: " . $conn->connect_error;
            $conn->close();
        } else {

            $hash = db_find_hash($conn, $username);
            $conn->close();
            if ($hash && password_verify($password, $hash)) {
                $_SESSION["username"] = $username;
                header("Location: ../home/home.php");
            } else {
                $login_error = "Invalid (username, password) pair";
            }
        }

    }
}

require_once "../common/header.php";
if ($username_error
    || $password_error
    || $login_error
    || $token_error
    || $db_error
    || $_SERVER["REQUEST_METHOD"] == "GET") {
    ?>
    <span class="error"><?php echo $login_error; ?></span><br>
    <span class="error"><?php echo $token_error; ?></span><br>
    <span class="error"><?php echo $db_error; ?></span><br>
    <form action="login.php" method="post">
        <label>Username <input type="text" name="username" required>
            <span class="error"><?php echo $username_error; ?></span>
        </label><br>
        <label>Password <input type="password" name="password" required>
            <span class="error"><?php echo $password_error; ?></span>
        </label><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>"/>
        <button type="submit" name="login_user">Log in</button>
    </form>
    <?php
}

require_once "../common/footer.php";
?>
