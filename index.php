<?php
include 'check_login.php';
include 'db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$perPage = 5;

$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

try {
    $query = $conn->prepare("SELECT * FROM events WHERE name LIKE :search ORDER BY $sortBy $sortOrder LIMIT :offset, :perPage");
    $query->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $query->bindValue(':offset', $offset, PDO::PARAM_INT);
    $query->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $query->execute();
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    $countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM events WHERE name LIKE :search OR description LIKE :search");
    $countQuery->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $countQuery->execute();
    $totalEvents = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalEvents / $perPage);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

try {
    $querys = $conn->prepare("SELECT e.id, e.name, e.capacity, 
                             (e.capacity - COUNT(ar.id)) AS available_capacity
                             FROM events e
                             LEFT JOIN attendee_registrations ar ON e.id = ar.event_id
                             GROUP BY e.id");
    $querys->execute();
    $attendees_events = $querys->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .btn-group .btn {
            margin-right: 0.25rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Event Management</a>
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

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Event List</h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fa fa-plus-circle me-2"></i>Add Event
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attendeeRegistrationModal">
                    <i class="fa fa-plus-circle me-2"></i>Register Attendee
                </button>
                <a href="attendee_report.php" class="btn btn-info ms-2">
                    <i class="fa fa-download me-2"></i>Download Report
                </a>
            </div>
        </div>

        <!-- Search and Sort -->
        <form method="GET" class="mb-4 d-flex justify-content-between">
            <input type="text" class="form-control w-50" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search by event name">

            <div class="d-flex ms-2 col-4">
                <select class="form-control" name="sort">
                    <option value="name" <?php if ($sortBy == 'name')
                        echo 'selected'; ?>>Name</option>

                    <option value="capacity" <?php if ($sortBy == 'capacity')
                        echo 'selected'; ?>>Capacity</option>
                </select>
                <select class="form-control ms-2" name="order">
                    <option value="asc" <?php if ($sortOrder == 'ASC')
                        echo 'selected'; ?>>Ascending</option>
                    <option value="desc" <?php if ($sortOrder == 'DESC')
                        echo 'selected'; ?>>Descending</option>
                </select>
                <button type="submit" class="btn btn-primary ms-2">Filter</button>
            </div>
        </form>

        <!-- Event Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Event List</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Name</th>
                            <th class="text-center">Capacity</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr data-id="<?php echo $event['id']; ?>">
                                <td class="text-center"><?php echo htmlspecialchars($event['name']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($event['capacity']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm me-2 viewEvent" data-bs-toggle="modal"
                                            data-bs-target="#viewEventModal" data-id="<?php echo $event['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($event['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                            data-capacity="<?php echo htmlspecialchars($event['capacity']); ?>"
                                            title="View Event">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm me-2 editEvent" data-bs-toggle="modal"
                                            data-bs-target="#editEventModal" data-id="<?php echo $event['id']; ?>"
                                            data-name="<?php echo $event['name']; ?>"
                                            data-description="<?php echo $event['description']; ?>"
                                            data-capacity="<?php echo $event['capacity']; ?>" title="Edit Event">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm deleteEvent"
                                            data-id="<?php echo $event['id']; ?>" title="Delete Event">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page == $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>


    <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewEventModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="viewEventName"></span></p>
                    <p><strong>Description:</strong> <span id="viewEventDescription"></span></p>
                    <p><strong>Capacity:</strong> <span id="viewEventCapacity"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addEventForm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="description" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eventCapacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="eventCapacity" name="capacity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="submitEventButton">Add Event</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editEventForm">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editEventId" name="id">
                        <div class="mb-3">
                            <label for="editEventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="editEventName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editEventDescription" name="description" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editEventCapacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="editEventCapacity" name="capacity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Attendee Registration Modal -->
    <div class="modal fade" id="attendeeRegistrationModal" tabindex="-1"
        aria-labelledby="attendeeRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendeeRegistrationModalLabel">Event Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registrationForm" method="POST" action="register_attendee.php">
                        <div class="mb-3">
                            <label for="event" class="form-label">Select Event:</label>
                            <select id="eventDropdown" name="event_id" class="form-select" required>
                                <option value="">--Select an Event--</option>
                                <?php foreach ($attendees_events as $event): ?>
                                    <option value="<?= $event['id'] ?>" data-capacity="<?= $event['available_capacity'] ?>">
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
                        <button type="submit" id="submitBtn" class="btn btn-primary w-100" disabled>Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.querySelectorAll('.registerAttendee').forEach(button => {
            button.addEventListener('click', function () {
                const eventId = this.getAttribute('data-id');
                const eventName = this.getAttribute('data-name');
                const availableCapacity = this.getAttribute('data-capacity');

                document.getElementById('event_id').value = eventId;
                document.getElementById('event_name').value = eventName;
                document.getElementById('available_capacity').value = availableCapacity;
            });
        });

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


        $(document).on("click", ".viewEvent", function () {
            let name = $(this).data("name");
            let description = $(this).data("description");
            let capacity = $(this).data("capacity");

            $("#viewEventName").text(name);
            $("#viewEventDescription").text(description);
            $("#viewEventCapacity").text(capacity);
        });


        $("#submitEventButton").click(function (e) {
            e.preventDefault();
            var formData = $("#addEventForm").serialize();

            $.ajax({
                url: "add_event.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        $('#addEventModal').modal('hide');
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert("Request failed: " + textStatus);
                }
            });
        });

        $(document).on('click', '.editEvent', function () {
            const eventId = $(this).data('id');
            const eventName = $(this).data('name');
            const eventDescription = $(this).data('description');
            const eventCapacity = $(this).data('capacity');

            $('#editEventId').val(eventId);
            $('#editEventName').val(eventName);
            $('#editEventDescription').val(eventDescription);
            $('#editEventCapacity').val(eventCapacity);

            $('#editEventModal').modal('show');
        });

        $('#editEventForm').on('submit', function (e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: "update_event.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        $('#editEventModal').modal('hide');
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function (error) {
                    alert("An error occurred: " + error);
                }
            });
        });

        $(document).on('click', '.deleteEvent', function () {
            const eventId = $(this).data('id');

            if (confirm('Are you sure you want to delete this event?')) {
                $.ajax({
                    url: 'delete_event.php',
                    type: 'POST',
                    data: { id: eventId },
                    success: function (response) {
                        if (response.success) {
                            $('tr[data-id="' + eventId + '"]').remove();
                            location.reload()
                                ;
                        } else {
                            alert('Failed to delete event');
                        }
                    }
                });
            }
        });
    </script>
</body>
<?php include('footer.php'); ?>

</html>