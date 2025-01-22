<?php
ob_start();

include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, return an error response
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add an event.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $capacity = $_POST['capacity'] ?? '';

    if (empty($name) || empty($description) || empty($capacity)) {
        // If any required fields are missing, return an error response
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit();
    }

    try {
        // Prepare the query and bind parameters
        $query = $conn->prepare("INSERT INTO events (name, description, capacity, user_id) VALUES (:name, :description, :capacity, :user_id)");
        $query->bindParam(':name', $name);
        $query->bindParam(':description', $description);
        $query->bindParam(':capacity', $capacity);
        $query->bindParam(':user_id', $user_id);

        // Execute the query
        if ($query->execute()) {
            // If successful, return a success response
            echo json_encode(['success' => true, 'message' => 'Event added successfully!']);
        } else {
            // If the query fails, return an error response
            echo json_encode(['success' => false, 'message' => 'Failed to add the event.']);
        }

    } catch (Exception $e) {
        // If an exception occurs, return an error response
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    // If the request method is not POST, return an error response
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>