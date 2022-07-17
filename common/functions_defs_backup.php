<?php

function array_to_ul_list($array, $show_keys = false)
{
    array_to_list($array, "ul", $show_keys);
}

function array_to_ol_list($array, $show_keys = false)
{
    array_to_list($array, "ol", $show_keys);
}

function array_to_list($array, $type, $show_keys = false)
{
    echo "<$type>\n";

    if ($show_keys) {
        foreach ($array as $key => $value) {
            echo "<li>$key ***index represents*** $value</li>\n";
        }
    } else {
        foreach ($array as $value) {
            echo "<li>$value</li>\n";
        }
    }

    echo "</$type>\n";
}

function array_to_dl_list($array)
{
    echo "<dl>\n";
    foreach ($array as $key => $value) {
        echo "<dt>$key</dt> <dd>$value</dd>\n";
    }
    echo "</dl>\n";
}

function clean_data($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// validation based on https://www.w3schools.com/php/php_form_url_email.asp
function validate_name(&$name, &$name_error) {
    if (empty(trim($_POST["name"]))) {
        $name_error = "Error: no name provided";
    } else {
        $name = clean_data($_POST["name"]);
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
            $name_error = "Error: only letters and white space allowed";
        }
    }
}

function validate_email(&$email, &$email_error) {
    if (empty($_POST["email"])) {
        $email_error = "Error: email is required";
    } else {
        $email = clean_data($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Error: invalid email format";
        }
    }
}

function validate_satisfaction(&$satisfaction, &$satisfaction_error) {
    if (empty($_POST["satisfaction"])) {
        $satisfaction_error = "Error: satisfaction is required";
    } else {
        $satisfaction = clean_data($_POST["satisfaction"]);
        if ($satisfaction != "very" && $satisfaction != "somewhat" && $satisfaction != "not") {
            $satisfaction_error = "Error: invalid satisfaction value";
        }
    }
}

function find_hash($username) {
    $password_file = fopen("passwords.csv", "r") or die("Unable to open file!");

    while (($data = fgetcsv($password_file, 1000, ",")) !== FALSE) {
        $count = count($data);
        if ($count >= 2 && $data[0] == $username) {
            fclose($password_file);
            return $data[1];
        }
    }
    fclose($password_file);
    return "";
}

//create user intro@localhost identified by 'intro';
//grant usage on *.* to intro ;
//GRANT ALL privileges ON `introdb`.* TO 'intro'@localhost;

function get_introdb_conn() {
    return new mysqli("localhost", "intro", "intro", "introdb");
}

function echo_table($result) {
    echo "<table><tr>";
    foreach ($result->fetch_fields() as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $col) {
            echo "<td>$col</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

function db_find_hash($conn, $username) {
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // if you expect only 1 result, don't loop, just get the only row
        $row = $result->fetch_row();
        return $row[0];
    } else {
        return "";
    }
}

function db_find_hash1($conn, $email) {
    $stmt = $conn->prepare("SELECT email FROM email WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // if you expect only 1 result, don't loop, just get the only row
        $row = $result->fetch_row();
        return $row[0];
    } else {
        return "";
    }
}


function insert_user($conn, $username, $password) {
    $stmt = $conn->prepare("insert into users (username, password) values (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if (!$stmt->execute()) {
        return $stmt->error;
    } else {
        return "";
    }
}

function insert_email($conn, $email) {
    $stmt = $conn->prepare("insert into email (email) values (?)");
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        return $stmt->error;
    } else {
        return "";
    }
}