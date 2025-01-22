<?php
include 'check_login.php'; // Ensure login is required
include 'db.php';

// Fetch all events to populate the dropdown
try {
    $query = $conn->prepare("SELECT * FROM events");
    $query->execute();
    $events = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Fetch attendees for a specific event if one is selected
$attendees = [];
$eventName = '';
if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
    $eventId = $_POST['event_id'];

    try {
        // Fetch event name
        $eventQuery = $conn->prepare("SELECT name FROM events WHERE id = :event_id");
        $eventQuery->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $eventQuery->execute();
        $event = $eventQuery->fetch(PDO::FETCH_ASSOC);
        $eventName = $event['name'] ?? 'Event Name Not Found';

        // Fetch attendees
        $query = $conn->prepare("SELECT * FROM attendee_registrations WHERE event_id = :event_id");
        $query->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $query->execute();
        $attendees = $query->fetchAll(PDO::FETCH_ASSOC);

        // If it's an AJAX request, return only the tbody part of the table
        if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
            if (!empty($attendees)) {
                foreach ($attendees as $attendee) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($attendee['attendee_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($attendee['attendee_email']) . '</td>';
                    echo '<td>' . htmlspecialchars($attendee['registered_at']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="3" class="text-center">No attendees found.</td></tr>';
            }
            exit(); // Exit after returning the HTML
        }

        // Set the headers to download the file with event name in the filename
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendees_report_' . $eventName . '.csv"');

        $output = fopen('php://output', 'w');

        // Add event name as the first row
        fputcsv($output, ['Event Name', $eventName]);

        // Write column headers
        fputcsv($output, ['Attendee Name', 'Email', 'Registration Date']);

        // Write attendee data to CSV
        foreach ($attendees as $attendee) {
            fputcsv($output, [
                $attendee['attendee_name'],
                $attendee['attendee_email'],
                $attendee['registered_at']
            ]);
        }

        fclose($output);
        exit(); // Stop further execution of the script

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}
?>


<?php include('header.php'); ?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center text-primary">Attendee Report</h2>
    <div class="mb-4">
        <a href="index.php" class="btn btn-info text-dark">Back To Dashboard</a>

    </div>


    <!-- Select Event Section -->
    <div class="card shadow-lg p-2">
        <label for="eventSelect" class="form-label fw-bold">Search and Select Event</label>
        <select id="eventSelect" class="form-select form-select-lg shadow-sm border-primary" aria-label="Select Event">
            <option value="" disabled selected>Select an Event</option>
            <?php foreach ($events as $event): ?>
                <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>


    <!-- Card for Table -->
    <div class="card shadow-lg p-4">
        <div class="card-body">
            <table class="table table-hover table-striped table-bordered align-middle" id="attendeeTable">
                <thead class="table-dark">
                    <tr>
                        <th>Attendee Name</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" class="text-center">Select an event to view attendees.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Form to download CSV for selected event -->
            <form action="attendee_report.php" method="post" id="downloadForm" style="display: none;">
                <input type="hidden" name="download_csv" value="1">
                <input type="hidden" name="event_id" id="event_id_input" value="">
                <button type="submit" class="btn btn-success mt-3">Download CSV</button>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function () {
        // Initialize Select2 on the event dropdown
        $('#eventSelect').select2({
            placeholder: 'Search for an event...',
            allowClear: true,
            width: '100%'
        });

        // Handle event selection
        $('#eventSelect').change(function () {
            var eventId = $(this).val();

            if (eventId) {
                // Load attendees for the selected event via AJAX
                $.ajax({
                    url: 'attendee_report.php',
                    type: 'POST',
                    data: {
                        event_id: eventId,
                        ajax: 1 // Indicate that this is an AJAX request
                    },
                    success: function (response) {
                        // Update the table body with the returned data
                        $('#attendeeTable tbody').html(response);

                        // Show the download form
                        $('#event_id_input').val(eventId);
                        $('#downloadForm').show();
                    }
                });
            } else {
                // Clear the table and hide the download form
                $('#attendeeTable tbody').html('<tr><td colspan="3" class="text-center">Select an event to view attendees.</td></tr>');
                $('#downloadForm').hide();
            }
        });
    });
</script>

<?php include('footer.php'); ?>