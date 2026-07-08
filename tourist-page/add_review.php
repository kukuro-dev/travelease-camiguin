<?php
session_start();
include "../db.php";

// Check tourist login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Get rental_id from booking
    $stmtBooking = $conn->prepare("SELECT rental_id FROM booking WHERE id = ? AND tourist_id = ?");
    $stmtBooking->bind_param("is", $booking_id, $tourist_id);
    $stmtBooking->execute();
    $resBooking = $stmtBooking->get_result();

    if ($resBooking->num_rows === 0) {
        die("Invalid booking.");
    }

    $booking = $resBooking->fetch_assoc();
    $rental_id = $booking['rental_id'];

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (rental_id, booking_id, tourist_id, rating, comment, date_created)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iisis", $rental_id, $booking_id, $tourist_id, $rating, $comment);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: reviews.php?success=1");
        exit;
    } else {
        die("Failed to add review.");
    }
} else {
    die("Invalid request.");
}
?>
