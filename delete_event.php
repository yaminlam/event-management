<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['id'];

    try {
        if (!$eventId || !is_numeric($eventId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
            exit;
        }

        $query = $conn->prepare("DELETE FROM events WHERE id = ?");
        $query->execute([$eventId]);

        if ($query->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>