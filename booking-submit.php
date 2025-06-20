<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $package_id = (int)$_POST['package_id'];
    $event_date = sanitize($conn, $_POST['event_date']);
    $special_requests = sanitize($conn, $_POST['special_requests']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Validate date
    $event_timestamp = strtotime($event_date);
    if ($event_timestamp === false || $event_timestamp < strtotime('today')) {
        die("Invalid event date");
    }

    // Check if the package exists
    $stmt = $conn->prepare("SELECT id FROM packages WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Invalid package selected");
    }
    $stmt->close();

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (customer_name, email, event_date, special_requests, package_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $email, $event_date, $special_requests, $package_id);

    if ($stmt->execute()) {
        header("Location: index.php?booking=success#booking");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>