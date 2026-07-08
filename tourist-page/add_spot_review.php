<?php
session_start();
include "../db.php";

// ✅ Check login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spot_id = intval($_POST['spot_id']);
    $tourist_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($spot_id && $rating && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO spot_reviews (spot_id, tourist_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $spot_id, $tourist_id, $rating, $comment);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Review added successfully!";
        } else {
            $_SESSION['error'] = "❌ Failed to add review. Please try again.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "⚠️ Please complete all fields.";
    }

    header("Location: spot-details.php?spot_id=" . $spot_id);
    exit;
} else {
    header("Location: explore.php");
    exit;
}
?>
