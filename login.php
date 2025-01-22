<?php
session_start();
include 'db.php';

// Display session message
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'danger';
    unset($_SESSION['message'], $_SESSION['message_type']);
}

try {
    // Fetch event details with available capacity
    $query = $conn->prepare("SELECT e.id, e.name, e.capacity, 
                             (e.capacity - COUNT(ar.id)) AS available_capacity
                             FROM events e
                             LEFT JOIN attendee_registrations ar ON e.id = ar.event_id
                             GROUP BY e.id");
    $query->execute();
    $events = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Handle login form submission
$emailErr = $passwordErr = $email = $password = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if (empty($_POST['email'])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST['email'];
    }

    if (empty($_POST['password'])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST['password'];
    }

    if (empty($emailErr) && empty($passwordErr)) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];

                header('Location: index.php');
                exit();
            } else {
                $passwordErr = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .nav-tabs .nav-link {
            color: #555;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            color: #007bff;
            border-color: #007bff;
        }

        .form-control {
            border-radius: 20px;
        }

        .btn-primary {
            border-radius: 25px;
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .text-muted {
            font-size: 0.9rem;
        }

        .wrapper {
            padding: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-12">
                <h1 class="text-center mb-4">Event Management System</h1>
                <ul class="nav nav-tabs justify-content-center mb-3" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login" role="tab">Login as
                            User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="attendee-tab" data-bs-toggle="tab" href="#attendee" role="tab">Register
                            for Event</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <div class="card p-4">
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Enter email"
                                        value="<?php echo htmlspecialchars($email); ?>" required>
                                    <small class="text-danger"><?php echo $emailErr; ?></small>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Password"
                                        required>
                                    <small class="text-danger"><?php echo $passwordErr; ?></small>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                                <div class="text-center mt-3">
                                    <a href="register.php" class="text-primary">Sign Up</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="attendee" role="tabpanel">
                        <div class="card p-4">
                            <form id="registrationForm" method="POST" action="register_attendee.php">
                                <div class="mb-3">
                                    <label for="event" class="form-label">Select Event:</label>
                                    <select id="eventDropdown" name="event_id" class="form-select" required>
                                        <option value="">--Select an Event--</option>
                                        <?php foreach ($events as $event): ?>
                                            <option value="<?= $event['id'] ?>"
                                                data-capacity="<?= $event['available_capacity'] ?>">
                                                <?= htmlspecialchars($event['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span id="availableCapacity" class="text-muted mt-2 d-block"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" id="name" name="attendee_name" class="form-control"
                                        placeholder="Enter your name" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="attendee_email" class="form-control"
                                        placeholder="Enter your email" required readonly>
                                </div>
                                <button type="submit" id="submitBtn" class="btn btn-primary w-100"
                                    disabled>Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#eventDropdown').on('change', function () {
                const selectedEvent = $(this).find(':selected');
                const availableCapacity = selectedEvent.data('capacity');

                if (availableCapacity === undefined || availableCapacity === null) {
                    $('#availableCapacity').text('No available capacity information.');
                    $('#name, #email').prop('readonly', true);
                    $('#submitBtn').prop('disabled', true);
                } else if (availableCapacity > 0) {
                    $('#availableCapacity').text(`Available Capacity: ${availableCapacity}`);
                    $('#name, #email').prop('readonly', false);
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#availableCapacity').text('Event is full.');
                    $('#name, #email').prop('readonly', true);
                    $('#submitBtn').prop('disabled', true);
                }
            });
        });
    </script>
</body>

</html>