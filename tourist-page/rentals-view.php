<?php 
session_start();
include_once __DIR__ . "/../db.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$categoryName = "All Rentals";
$where = " WHERE availability='available' ";
$params = [];
$types = "";

// Search filter
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $where .= " AND (title LIKE ? OR description LIKE ? OR location_address LIKE ? OR city LIKE ? OR province LIKE ?)";
    $search = "%{$_GET['query']}%";
    $params = array_fill(0, 5, $search);
    $types = str_repeat("s", 5);
}

// Category filter
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $where .= " AND category_id=?";
    $params[] = $_GET['category_id'];
    $types .= "i";

    $stmt = $conn->prepare("SELECT name FROM categories WHERE id=?");
    $stmt->bind_param("i", $_GET['category_id']);
    $stmt->execute();
    $catRes = $stmt->get_result();
    if ($catRow = $catRes->fetch_assoc()) {
        $categoryName = htmlspecialchars($catRow['name']);
    }
}

// Fetch rentals
$sql = "SELECT r.*, u.contact_number 
        FROM rentals r
        JOIN users u ON r.provider_id=u.user_id
        $where ORDER BY r.date_created DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">
<style>
.star-rating {
    color: #f39c12;
    font-size: 1rem;
}
</style>
</head>
<body>

<?php include_once __DIR__ . "/tourist-header.php"; ?>

<div class="container mt-4">
    <h2>Available Rentals: <?= $categoryName ?></h2>
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($r = $result->fetch_assoc()): ?>
                <?php
                // Fetch average rating
                $stmt2 = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE rental_id=?");
                $stmt2->bind_param("i", $r['id']);
                $stmt2->execute();
                $reviewRes = $stmt2->get_result();
                $reviewRow = $reviewRes->fetch_assoc();
                $avgRating = $reviewRow['avg_rating'] ? round($reviewRow['avg_rating'],1) : 0;
                $totalReviews = $reviewRow['total_reviews'];
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= !empty($r['profile_image']) && file_exists($r['profile_image']) ? htmlspecialchars($r['profile_image']) : '../image/default.png' ?>" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($r['title']) ?></h5>
                            <p><?= htmlspecialchars($r['description']) ?></p>
                            <p><strong>₱<?= number_format($r['price'],2) ?></strong></p>
                            <p><?= htmlspecialchars($r['city'] . ", " . $r['province']) ?></p>
                            <p>Contact: <?= htmlspecialchars($r['contact_number']) ?></p>

                            <!-- Ratings -->
                            <p class="star-rating">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star<?= $i <= round($avgRating) ? '' : '-half-alt' ?>"></i>
                                <?php endfor; ?>
                                (<?= $avgRating ?> / 5, <?= $totalReviews ?> reviews)
                            </p>

                            <?php if (strtolower(trim($r['availability']))==="available"): ?>
                                <a href="booking.php?rental_id=<?= $r['id'] ?>" class="btn btn-success w-100">Book Now</a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>Not Available</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No rentals found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>
</body>
</html>
