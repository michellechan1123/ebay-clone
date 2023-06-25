<?php include_once("header.php") ?>
<?php require_once("utilities.php") ?>
<?php

//session_start();
$mysqli = get_connection();
$errors=array();


// STEP 0: Processing data from the log-in form using $_POST variables
if (isset($_POST['login_user'])) {

    // STEP 1: receive all input values from the form
    $email = $_POST['email'];
    $login_password = $_POST['password'];

    // STEP 2: Verify log-in details
    $login_query= "SELECT * 
    FROM users 
    JOIN roles r USING (role_id)
    WHERE email_address = ?";

    $stmt = $mysqli->prepare($login_query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // STEP 2.1: Check if the user exists (using email)
    if($result->num_rows == 0) {  //the login email does not exist
        array_push($errors, "User not found.");
        $user_err = 1;
        echo ("user not found");
        header("refresh:5;url=browse.php");
    }else {
        // if email exists, check other log-in credentials

        $row = mysqli_fetch_assoc($result);
        $password_hash = $row['password_hash'];
        $account_type = $row['role_name'];
        $firstname = $row['firstname'];
        $email = $row['email_address'];
        $user_id = $row['user_id'];

        // STEP 2.2: check if the log-in password is correct
        if (password_verify($login_password, $password_hash)) {

            // STEP 3: create SESSION settings if log-in details are correct
            $_SESSION['logged_in'] = true;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['account_type'] = $account_type;
            $_SESSION['email'] = $email;
            echo "You are now logged in! You will be redirected shortly.";
            header("refresh:5;url=browse.php");
        } else {
            array_push($errors, "Invalid password.");
            echo "\n Invalid email and password combination. Redirecting to home page now...";
            header("refresh:3;url=browse.php");
        }
    }
}



?>