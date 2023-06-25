
<?php require_once("utilities.php") ?>

<?php


$mysqli = get_connection(); //query data for unique user verification

// Initialise user variables
$email = $firstname = $lastname = $password = $confirm_password = '';
$email_err = $unique_err = $password_err = $firstname_err = $lastname_err = $confirm_password_err = 0;
$errors = array();


// Processing date from the registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // STEP 0: receive all input values from the form
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $account_type = $_POST['accountType'];


    //STEP 1: Form validation

    // STEP 1.1: verify email address: in right format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Invalid format. Please enter your full email");
        $email_err = 1;
    } else {
        // STEP 1.2: ensure email is not repeated- query the data
        $unique_user_check_sql = "SELECT email_address FROM users WHERE email_address = '$email'";
        $stmt = $mysqli->prepare($unique_user_check_sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            array_push($errors, "This email is already registered. Please log in or use another email.");
            $unique_err = 1;
        }

    }

    // STEP 1.3: verify firstname and lastname: not empty and in right format
    if (!preg_match('/^[a-zA-Z-]+$/', $_POST["firstname"])) {
        array_push($errors, "First name should only contain letters.");
        $firstname_err =1;
    }
    if (!preg_match('/^[a-zA-Z-]+$/', $_POST["lastname"])) {
        array_push($errors, "Last name should only contain letters.");
        $lastname_err =1;
    }

    // STEP 1.4: verify passwords: should have at least 6 characters
    if (strlen($_POST["password"]) < 6) {
        array_push($errors, "Password must have at least 6 characters.");
        $password_err= 1;
    }

    // STEP 1.5: verify password confirmation: two passwords entered should match
    if ($password != $confirm_password) {
        array_push($errors, "Passwords did not match.");
        $confirm_password_err= 1;
    }

    // STEP 2: Check input errors before inserting data into database
    if (count($errors) == 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($account_type=='buyer') {
            $role_id = 1;
        } elseif ($account_type=='seller'){
            $role_id = 2;
        } else {
            echo "Error: account type not selected";
            return;
        }
        $query = "INSERT INTO users (firstname, lastname, email_address, password_hash, role_id) 
                VALUES('$firstname', '$lastname', '$email','$password_hash','$role_id')";
        $stmt = $mysqli->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();


        if(!is_null($result)){
            // Redirect to browse page
            echo('<div class="text-center">Registration successful! You will be redirected shortly.</div>');
            header("refresh:5;url=browse.php");
            //header("refresh:2; url= browse.php?registered=1");
        } else{
            echo "Oops! Data is not saved. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }else{
        // Redirect to register page if not registered successfully
        header('location:register.php?success=0&&email='.$email_err.'&&uni='.$unique_err.'&&first='.$firstname_err.'&&last='.$lastname_err.'&&pass='.$password_err.'&&confirm='.$confirm_password_err);

    }


}
?>

