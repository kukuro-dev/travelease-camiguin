<?php
session_start();
include "../db.php"; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'provider') {
    header("Location: ../users/user-login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Update or create payment
if (isset($_POST['save_payment'])) {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'];
    $booking_id = $_POST['booking_id'];

    if ($id == 0) {
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, tourist_id, provider_id, amount, payment_method, status, date_created)
                                SELECT b.id, b.tourist_id, r.provider_id, b.total_price, 'Owner', ?, NOW()
                                FROM booking b
                                INNER JOIN rentals r ON b.rental_id = r.id
                                WHERE b.id = ?");
        $stmt->bind_param("si", $status, $booking_id);
        if ($stmt->execute()) {
            $success = "Payment status saved successfully!";
        } else {
            $error = "Failed to save payment status.";
        }
    } else {
        $stmt = $conn->prepare("UPDATE payments SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $success = "Payment status updated successfully!";
        } else {
            $error = "Failed to update payment status.";
        }
    }
}

if (isset($_POST['delete_payment'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Payment record deleted successfully!";
    } else {
        $error = "Failed to delete payment record.";
    }
}

// Filter parameters
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch bookings for this provider with filters
$sql = "
    SELECT b.id AS booking_id, b.fullname AS tourist_name, b.total_price, r.title AS rental_title,
           b.guests, b.units, b.booking_date, b.date_created,
           IFNULL(p.status,'Not Paid') AS payment_status, IFNULL(p.id,0) AS id, IFNULL(p.amount, b.total_price) AS amount
    FROM booking b
    INNER JOIN rentals r ON b.rental_id = r.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE r.provider_id = ?
";

$params = [$provider_id];
$types = "s";

if (!empty($filter_status)) {
    if ($filter_status === 'Not Paid') {
        $sql .= " AND (p.status IS NULL OR p.status = 'Not Paid')";
    } else {
        $sql .= " AND p.status = ?";
        $params[] = $filter_status;
        $types .= "s";
    }
}

if (!empty($search_query)) {
    $sql .= " AND (b.fullname LIKE ? OR r.title LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY b.date_created DESC";

$bookings = $conn->prepare($sql);
$bookings->bind_param($types, ...$params);
$bookings->execute();
$bookings_result = $bookings->get_result();

// Calculate statistics
$total_income = 0;
$total_pending = 0;
$paid_count = 0;
$unpaid_count = 0;

$temp_results = [];
while ($row = $bookings_result->fetch_assoc()) {
    $temp_results[] = $row;
    if ($row['payment_status'] === 'Paid') {
        $total_income += $row['amount'];
        $paid_count++;
    } else {
        $total_pending += $row['amount'];
        $unpaid_count++;
    }
}

$total_bookings = count($temp_results);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings & Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        body.loaded {
            opacity: 1;
        }

        .page-wrapper {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stat-icon.income {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-icon.paid {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .stat-icon.unpaid {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .stat-count {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 3px;
        }

        /* Search and Filter */
        .controls-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .controls-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .control-group {
            flex: 1;
            min-width: 200px;
        }

        .control-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .control-input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .control-input:focus {
            outline: none;
            border-color: #2c3e50;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
        }

        .btn-clear {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
            text-decoration: none;
        }

        .btn-clear:hover {
            background: #dc2626;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
        }

        thead th {
            padding: 16px 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody td {
            padding: 14px 12px;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-not-paid {
            background: #fee2e2;
            color: #991b1b;
        }

        .payment-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .status-select {
            padding: 6px 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .status-select:focus {
            outline: none;
            border-color: #2c3e50;
        }

        .btn-save {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-save:hover {
            transform: scale(1.05);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-delete:hover {
            transform: scale(1.05);
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 64px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #4a5568;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #718096;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .page-wrapper {
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 22px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 20px;
            }

            .controls-row {
                flex-direction: column;
            }

            .control-group {
                width: 100%;
                min-width: 100%;
            }

            .btn-primary,
            .btn-clear {
                width: 100%;
                justify-content: center;
            }

            /* Mobile card layout */
            .table-wrapper {
                overflow-x: visible;
            }

            table {
                border: 0;
            }

            thead {
                display: none;
            }

            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                overflow: hidden;
                background: white;
            }

            tbody tr:hover {
                background: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 15px;
                border-bottom: 1px solid #f3f4f6;
                text-align: right;
            }

            tbody td:last-child {
                border-bottom: none;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #2c3e50;
                font-size: 12px;
                text-transform: uppercase;
                text-align: left;
                flex-shrink: 0;
                margin-right: 10px;
            }

            tbody td:first-child {
                background: linear-gradient(135deg, #2c3e50, #34495e);
                color: white;
                font-weight: 700;
                justify-content: center;
            }

            tbody td:first-child::before {
                color: white;
            }

            .payment-form {
                flex-direction: column;
                width: 100%;
            }

            .status-select,
            .btn-save,
            .btn-delete {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 20px;
            }

            .stat-value {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
<?php include "user-header.php"; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-money-bill-wave"></i>
            Bookings & Payments
        </h1>
        <p class="page-subtitle">Manage your bookings and track payment status</p>
    </div>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon income">
                <i class="fas fa-sack-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Income</div>
                <div class="stat-value">₱ <?= number_format($total_income, 2) ?></div>
                <div class="stat-count"><?= $paid_count ?> paid booking<?= $paid_count != 1 ? 's' : '' ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Amount</div>
                <div class="stat-value">₱ <?= number_format($total_pending, 2) ?></div>
                <div class="stat-count"><?= $unpaid_count ?> unpaid booking<?= $unpaid_count != 1 ? 's' : '' ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon paid">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Paid Bookings</div>
                <div class="stat-value"><?= $paid_count ?></div>
                <div class="stat-count">of <?= $total_bookings ?> total</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon unpaid">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Unpaid Bookings</div>
                <div class="stat-value"><?= $unpaid_count ?></div>
                <div class="stat-count">of <?= $total_bookings ?> total</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="controls-container">
        <form method="get" class="controls-row">
            <div class="control-group">
                <label class="control-label">
                    <i class="fas fa-search"></i> Search
                </label>
                <input type="text" name="search" class="control-input" placeholder="Search by tourist or rental name..." value="<?= htmlspecialchars($search_query) ?>">
            </div>

            <div class="control-group">
                <label class="control-label">
                    <i class="fas fa-filter"></i> Payment Status
                </label>
                <select name="filter_status" class="control-input">
                    <option value="">All Statuses</option>
                    <option value="Paid" <?= $filter_status === 'Paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="Not Paid" <?= $filter_status === 'Not Paid' ? 'selected' : '' ?>>Not Paid</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i> Search
            </button>

            <?php if (!empty($search_query) || !empty($filter_status)): ?>
                <a href="?" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="table-container">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tourist</th>
                        <th>Rental</th>
                        <th>Booking Date</th>
                        <th>Guests / Units</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($temp_results)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($temp_results as $row): ?>
                            <tr>
                                <td data-label="#"><?= $counter++ ?></td>
                                <td data-label="Tourist"><?= htmlspecialchars($row['tourist_name']) ?></td>
                                <td data-label="Rental"><?= htmlspecialchars($row['rental_title']) ?></td>
                                <td data-label="Booking Date"><?= date("M d, Y", strtotime($row['booking_date'])) ?></td>
                                <td data-label="Guests / Units">
                                    <?php 
                                    $details = [];
                                    if ($row['guests'] > 0) $details[] = "Guests: {$row['guests']}";
                                    if ($row['units'] > 0) $details[] = "Units: {$row['units']}";
                                    echo !empty($details) ? implode(" | ", $details) : "N/A";
                                    ?>
                                </td>
                                <td data-label="Amount"><strong>₱ <?= number_format($row['amount'], 2) ?></strong></td>
                                <td data-label="Status">
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['payment_status'])) ?>">
                                        <?= $row['payment_status'] === 'Paid' ? '✓ Paid' : '⏳ Not Paid' ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <form method="post" class="payment-form">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                        <select name="status" class="status-select">
                                            <option value="Not Paid" <?= $row['payment_status']=='Not Paid'?'selected':'' ?>>Not Paid</option>
                                            <option value="Paid" <?= $row['payment_status']=='Paid'?'selected':'' ?>>Paid</option>
                                        </select>
                                        <button type="submit" name="save_payment" class="btn-save" title="Save">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <?php if($row['id'] != 0): ?>
                                            <button type="submit" name="delete_payment" class="btn-delete" title="Delete" onclick="return confirm('Delete this payment record?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No Bookings Found</h3>
                                    <p>No bookings match your search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "user-footer.php"; ?>

<script>
// Prevent FOUC
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>
</body>
</html>