<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendee Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Event Management</a>
            <div class="d-flex">
                <?php if (isset($_SESSION['email'])): ?>
                    <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary ms-2">Login</a>
                    <a href="register.php" class="btn btn-secondary ms-2">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>