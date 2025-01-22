<?php
include 'db.php'; // Ensure db.php doesn't output anything

header('Content-Type: application/json');

// Your script logic here
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $eventId = $_POST['id'];
        $eventName = $_POST['name'];
        $eventDescription = $_POST['description'];
        $eventCapacity = $_POST['capacity'];

        $query = $conn->prepare("UPDATE events SET name = ?, description = ?, capacity = ? WHERE id = ?");
        $query->execute([$eventName, $eventDescription, $eventCapacity, $eventId]);

        echo json_encode([
            "success" => true,
            "message" => "Event updated successfully!",
            "event" => [
                "id" => $eventId,
                "name" => $eventName,
                "description" => $eventDescription,
                "capacity" => $eventCapacity
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}
?>