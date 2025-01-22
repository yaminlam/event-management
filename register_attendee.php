<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $attendee_name = $_POST['attendee_name'];
    $attendee_email = $_POST['attendee_email'];

    try {
        // Check if capacity is available
        $query = $conn->prepare("SELECT capacity, (capacity - COUNT(ar.id)) AS available_capacity
                                 FROM events e
                                 LEFT JOIN attendee_registrations ar ON e.id = ar.event_id
                                 WHERE e.id = :event_id
                                 GROUP BY e.id");
        $query->bindParam(':event_id', $event_id);
        $query->execute();
        $event = $query->fetch(PDO::FETCH_ASSOC);

        if ($event && $event['available_capacity'] > 0) {
            // Register the attendee
            $insertQuery = $conn->prepare("INSERT INTO attendee_registrations (event_id, attendee_name, attendee_email)
                                           VALUES (:event_id, :attendee_name, :attendee_email)");
            $insertQuery->bindParam(':event_id', $event_id);
            $insertQuery->bindParam(':attendee_name', $attendee_name);
            $insertQuery->bindParam(':attendee_email', $attendee_email);
            $insertQuery->execute();

            $_SESSION['message'] = "Registration successful! Welcome, " . htmlspecialchars($attendee_name) . ".";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Registration failed. Event is full.";
            $_SESSION['message_type'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to the registration form
header("Location: login.php");
exit();
?>