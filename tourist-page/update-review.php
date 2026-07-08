<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    $stmt = $conn->prepare("UPDATE reviews SET rating=?, comment=?, date_created=NOW() WHERE id=? AND tourist_id=?");
    $stmt->bind_param("issi", $rating, $comment, $review_id, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: reviews.php");
    exit;
}
?>
