    <aside >
        <h2 >Blogs</h2>
        <ul>
            <li><a href="https://uworks.ca/2020/06/15/%e5%b0%8f%e8%af%97%e4%bd%9c%e4%ba%8emaisonneuve%e5%85%ac%e5%9b%ad/
">Blog June 15th, 2020</a></li>
            <li><a href="https://uworks.ca/2020/05/19/%e6%b3%95%e8%af%ad%e4%b8%ad%e5%ad%a6-%e9%b9%85%e6%a0%a1-college-jean-eudes/
">Blog May 19th, 2020</a></li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
            <li>Blog Nov.20th</li>
        </ul>

        <?php
        // lifetime: 1 day (24 hours/day, 60 minutes/hour, and 60 seconds/minute)
        session_start(['cookie_lifetime' => 24 * 60 * 60,]) or die("Cannot start the session. Are cookies enabled?");
        if (!isset($_SESSION["token"])) {
            $_SESSION["token"] = bin2hex(random_bytes(24));
        }

        require_once "../common/functions_defs.php";
        $sub_title = "Email_subscribe";
        $email = $email_error = $sub_alert = $trim_email="";
        $token_error = $db_error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
                $token_error = "Error: cannot process the form";
            }

            $trim_email=trim($_POST["email"]);
            switch ($trim_email) {
                case empty($trim_email):
                    $email_error = "Error: no email provided";
                    break;
                case (!strpos($trim_email,"@")):
                case (!strpos($trim_email, ".")):
                case (strpos($trim_email,"@") == 0):
                case strpos($trim_email,".")==(strlen($trim_email)-1):
                    $email_error = "Error: invalid email provided";
                    break;
                default:
                    $email="";
            }

            if ($email_error == "" && $token_error == "") {
                $email = clean_data($_POST["email"]);

                $conn = get_introdb_conn();
                if ($conn->connect_error) {
                    $db_error = "Connection failed: " . $conn->connect_error;
                    $conn->close();
                } else {
                    $hash = db_find_hash1($conn, $email);
                    if ($hash) {  // email already exists?
                        $sub_alert = "This email had been subscribed!  Thank you!";

                    } else {
                        if (insert_email($conn, $email)) {
                            $db_error = "Data problem: " . insert_email($conn, $email);
                        } else {
                        $sub_alert = "You have successfully subscribed!  Thank you!";
                        }
                        $conn->close();

                    }
                }
            }
        }


        if ($email_error
            || $token_error
            || $db_error
            || $sub_alert
            || $_SERVER["REQUEST_METHOD"] == "GET") {
            ?>
            <hr>
            <span class="error"><?php echo $token_error; ?></span><br>
            <span class="error"><?php echo $db_error; ?></span><br>
            <span class="error"><?php echo $email_error; ?></span><br>
            <span class="alert"><?php echo $sub_alert; ?></span><br>
            <form method="post">
                <label>email <input type="text" name="email" required>
                </label><br>
                <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>"/>
                <button type="submit">Subscribe</button>
            </form>
        <?php } ?>
    </aside>
