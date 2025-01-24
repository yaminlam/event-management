<?php
ob_start();
include 'check_login.php';
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add an event.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $capacity = $_POST['capacity'] ?? '';

    if (empty($name) || empty($description) || empty($capacity)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit();
    }

    try {
        $query = $conn->prepare("INSERT INTO events (name, description, capacity, user_id) VALUES (:name, :description, :capacity, :user_id)");
        $query->bindParam(':name', $name);
        $query->bindParam(':description', $description);
        $query->bindParam(':capacity', $capacity);
        $query->bindParam(':user_id', $user_id);

        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Event added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add the event.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>