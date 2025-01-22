<?php
ob_start();

session_start(); // Start the session at the very top

include 'db.php';

if (isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

$nameErr = $emailErr = $passwordErr = $confirmPasswordErr = $username = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize validation flags
    $valid = true;

    // Validate username
    if (empty($_POST["username"])) {
        $nameErr = "Username is required";
        $valid = false;
    } else {
        $username = $_POST['username'];
    }

    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
        $valid = false;
    } else {
        $email = $_POST['email'];

        // Check if email already exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            $emailErr = "Email is already taken";
            $valid = false;
        }
    }

    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
        $valid = false;
    } else {
        $password = $_POST['password'];
    }

    // Validate confirm password
    if (empty($_POST["confirm_password"])) {
        $confirmPasswordErr = "Confirm password is required";
        $valid = false;
    } elseif ($_POST["password"] !== $_POST["confirm_password"]) {
        $confirmPasswordErr = "Passwords do not match";
        $valid = false;
    }

    // Proceed if validation is successful
    if ($valid) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare the SQL query to insert the new user
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
            $conn->exec($sql);

            // Set a success message in the session
            $_SESSION['message'] = 'Registration successful! You can now log in.';
            $_SESSION['message_type'] = 'success';  // Set the message type

            // Redirect to login page
            header("Location: login.php");
            exit();  // Ensure no further code is executed after the redirect
        } catch (PDOException $e) {
            // Set error message in session
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';  // Set the message type

            // Redirect back to the registration page
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card register-card p-4 col-md-6">
            <h2 class="text-center mb-4">Create an Account</h2>

            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control <?php echo $nameErr ? 'is-invalid' : ''; ?>"
                        placeholder="Your Username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                    <div class="invalid-feedback"><?php echo $nameErr; ?></div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control <?php echo $emailErr ? 'is-invalid' : ''; ?>"
                        placeholder="Enter Email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <div class="invalid-feedback"><?php echo $emailErr; ?></div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password"
                        class="form-control <?php echo $passwordErr ? 'is-invalid' : ''; ?>" placeholder="Password">
                    <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password"
                        class="form-control <?php echo $confirmPasswordErr ? 'is-invalid' : ''; ?>"
                        placeholder="Confirm Password">
                    <div class="invalid-feedback"><?php echo $confirmPasswordErr; ?></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block w-100">Register</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-link">Already have an account? Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>