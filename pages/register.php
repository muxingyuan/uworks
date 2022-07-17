<?php
// lifetime: 1 day (24 hours/day, 60 minutes/hour, and 60 seconds/minute)
session_start(['cookie_lifetime' => 24*60*60,]) or die("Cannot start the session. Are cookies enabled?");
if (!isset($_SESSION["token"])) {
    $_SESSION["token"] = bin2hex(random_bytes(24));
}

require_once "../common/functions_defs.php";
$title = "Register";
$username = $password1 = $password2 = "";
$username_error = $password1_error = $password2_error = $register_error = $token_error = "";
// starting to have too many error variables, consider using an errors associative array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $token_error = "Error: cannot process the form";
    }
    if (empty(trim($_POST["username"]))) {
        $username_error = "Error: no user name provided";
    }
    if (empty(trim($_POST["password1"]))) {
        $password1_error = "Error: no password provided";
    }
    if (empty(trim($_POST["password2"]))) {
        $password2_error = "Error: no password provided";
    }
    $password1 = clean_data($_POST["password1"]);
    $password2 = clean_data($_POST["password2"]);
    if ($password1 != $password2) {
        $register_error = "The 2 passwords don't match!";
    }
    elseif ($username_error == "" && $password1_error == ""
        && $password2_error == "" && $token_error == "") {
        $username = clean_data($_POST["username"]);

        $conn = get_introdb_conn();
        if ($conn->connect_error) {
            $db_error = "Connection failed: " . $conn->connect_error;
            $conn->close();
        } else {
            $hash = db_find_hash($conn, $username);
            if ($hash) {  // user already exists?
                $register_error = "This user name already exists!";
            } else {
                $options = ['cost' => 12,];
                $hash = password_hash($password1, PASSWORD_BCRYPT, $options);
                if (insert_user($conn, $username, $hash)) {
                    $register_error = "Cannot create this user ";
                } else {
                    $_SESSION["username"]=$username;
                    header("Location: ../home/home.php");
                }
            }
        }
    }
}

require_once "../common/header.php";
if ($username_error
    || $password1_error
    || $password2_error
    || $register_error
    || $token_error
    || $_SERVER["REQUEST_METHOD"] == "GET") {
    ?>
    <span class="error"><?php echo $register_error; ?></span><br>
    <span class="error"><?php echo $token_error; ?></span><br>
    <form action="register.php" method="post">
        <label>Username <input type="text" name="username" required>
            <span class="error"><?php echo $username_error; ?></span>
        </label><br>
        <label>Password <input type="password" name="password1" required>
            <span class="error"><?php echo $password1_error; ?></span>
        </label><br>
        <label>Enter password again <input type="password" name="password2" required>
            <span class="error"><?php echo $password2_error; ?></span>
        </label><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>"/>
        <button type="submit">Register</button>
    </form>
    <?php
}

require_once "../common/footer.php";
?>
