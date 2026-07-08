<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'provider') {
    header("Location: ../users/user-login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$error = "";
$success = "";

if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $update_sql = "UPDATE booking b INNER JOIN rentals r ON b.rental_id = r.id SET b.status = ? WHERE b.id = ? AND r.provider_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sis", $new_status, $booking_id, $provider_id);
    if ($stmt->execute()) {
        $success = "Booking status updated successfully.";
    } else {
        $error = "Failed to update booking status.";
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE b FROM booking b INNER JOIN rentals r ON b.rental_id = r.id WHERE b.id = ? AND r.provider_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("is", $delete_id, $provider_id);
    if ($stmt->execute()) {
        $success = "Booking deleted successfully.";
    } else {
        $error = "Failed to delete booking.";
    }
}

$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_rental = isset($_GET['filter_rental']) ? $_GET['filter_rental'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$sql = "SELECT b.id AS booking_id, r.title AS rental_title, b.fullname AS tourist_name, b.contact_number, b.form_type, b.place_staying, b.booking_date, b.start_time, b.end_time, b.status, b.date_created, b.guests, b.units, b.total_price FROM booking b INNER JOIN rentals r ON b.rental_id = r.id WHERE r.provider_id = ?";

$params = [$provider_id];
$types = "s";

if (!empty($filter_status)) {
    $sql .= " AND b.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if (!empty($filter_rental)) {
    $sql .= " AND r.id = ?";
    $params[] = $filter_rental;
    $types .= "i";
}
if (!empty($search_query)) {
    $sql .= " AND (b.fullname LIKE ? OR b.contact_number LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}
if (!empty($date_from)) {
    $sql .= " AND b.booking_date >= ?";
    $params[] = $date_from;
    $types .= "s";
}
if (!empty($date_to)) {
    $sql .= " AND b.booking_date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY b.date_created DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_bookings = 0;
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;
$total_revenue = 0;

$temp_results = [];
while ($row = $result->fetch_assoc()) {
    $temp_results[] = $row;
    $total_bookings++;
    $status_lower = strtolower($row['status']);
    if ($status_lower == 'pending') $pending_count++;
    elseif ($status_lower == 'approved') {
        $approved_count++;
        $total_revenue += $row['total_price'];
    }
    elseif ($status_lower == 'rejected') $rejected_count++;
}

$rentals_query = $conn->prepare("SELECT id, title FROM rentals WHERE provider_id = ? ORDER BY title");
$rentals_query->bind_param("s", $provider_id);
$rentals_query->execute();
$rentals_result = $rentals_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #fff; min-height: 100vh; opacity: 0; transition: opacity 0.3s; }
        body.loaded { opacity: 1; }
        .page-wrapper { padding: 20px; max-width: 1600px; margin: 0 auto; }
        .page-header { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(44,62,80,0.3); }
        .page-title { font-size: 28px; font-weight: 700; margin: 0 0 5px 0; display: flex; align-items: center; gap: 12px; }
        .page-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 15px; transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .stat-icon { width: 55px; height: 55px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .stat-icon.total { background: linear-gradient(135deg, #2c3e50, #34495e); }
        .stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.approved { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.rejected { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-icon.revenue { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-content { flex: 1; }
        .stat-label { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .stat-value { font-size: 22px; font-weight: 700; color: #1f2937; }
        .filters-container { background: white; padding: 20px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .filters-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-label { font-size: 13px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px; }
        .filter-input { padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; }
        .filter-input:focus { outline: none; border-color: #2c3e50; }
        .filters-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-filter { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s; text-decoration: none; }
        .btn-filter:hover { transform: translateY(-2px); }
        .btn-clear { background: #ef4444; color: white; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s; text-decoration: none; }
        .btn-clear:hover { background: #dc2626; }
        .btn-export { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.2s; }
        .btn-export:hover { transform: translateY(-2px); }
        .table-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; }
        thead th { padding: 16px 12px; text-align: left; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        tbody tr { border-bottom: 1px solid #e5e7eb; transition: background 0.2s; }
        tbody tr:hover { background: #f9fafb; }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 14px 12px; font-size: 14px; color: #374151; vertical-align: middle; }
        .status-select { padding: 6px 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; min-width: 110px; }
        .status-select:focus { outline: none; border-color: #2c3e50; }
        .btn-delete { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; }
        .btn-delete:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.4); }
        .empty-state { padding: 60px 20px; text-align: center; }
        .empty-state i { font-size: 64px; color: #cbd5e0; margin-bottom: 20px; }
        .empty-state h3 { color: #4a5568; margin-bottom: 10px; }
        .empty-state p { color: #718096; }
        @media (max-width: 1024px) { .page-wrapper { padding: 15px; } .stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); } }
        @media (max-width: 768px) { 
            .page-header { padding: 20px; } 
            .page-title { font-size: 22px; } 
            .stats-grid { grid-template-columns: 1fr; } 
            .filters-row { grid-template-columns: 1fr; } 
            .filters-actions { flex-direction: column; } 
            .btn-filter, .btn-clear, .btn-export { width: 100%; justify-content: center; } 
            .table-wrapper { overflow-x: visible; } 
            table { border: 0; } 
            thead { display: none; } 
            tbody tr { display: block; margin-bottom: 20px; border: 2px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: white; } 
            tbody tr:hover { background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); } 
            tbody td { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #f3f4f6; text-align: right; } 
            tbody td:last-child { border-bottom: none; } 
            tbody td::before { content: attr(data-label); font-weight: 600; color: #2c3e50; font-size: 12px; text-transform: uppercase; text-align: left; flex-shrink: 0; margin-right: 10px; } 
            tbody td:first-child { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; font-weight: 700; justify-content: center; } 
            tbody td:first-child::before { color: white; } 
            .status-select, .btn-delete { width: 100%; justify-content: center; } 
        }
        @media (max-width: 480px) { .page-title { font-size: 20px; } .stat-value { font-size: 18px; } }
    </style>
</head>
<body>
<?php include "user-header.php"; ?>
<div class="page-wrapper">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-calendar-check"></i> Booking Requests</h1>
        <p class="page-subtitle">Manage and track all your booking requests</p>
    </div>
    <?php if ($error != ""): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success != ""): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon total"><i class="fas fa-list"></i></div><div class="stat-content"><div class="stat-label">Total Bookings</div><div class="stat-value"><?= $total_bookings ?></div></div></div>
        <div class="stat-card"><div class="stat-icon pending"><i class="fas fa-clock"></i></div><div class="stat-content"><div class="stat-label">Pending</div><div class="stat-value"><?= $pending_count ?></div></div></div>
        <div class="stat-card"><div class="stat-icon approved"><i class="fas fa-check-circle"></i></div><div class="stat-content"><div class="stat-label">Approved</div><div class="stat-value"><?= $approved_count ?></div></div></div>
        <div class="stat-card"><div class="stat-icon rejected"><i class="fas fa-times-circle"></i></div><div class="stat-content"><div class="stat-label">Rejected</div><div class="stat-value"><?= $rejected_count ?></div></div></div>
        <div class="stat-card"><div class="stat-icon revenue"><i class="fas fa-peso-sign"></i></div><div class="stat-content"><div class="stat-label">Total Revenue</div><div class="stat-value">₱ <?= number_format($total_revenue, 2) ?></div></div></div>
    </div>
    <div class="filters-container">
        <form method="get">
            <div class="filters-row">
                <div class="filter-group"><label class="filter-label"><i class="fas fa-search"></i> Search</label><input type="text" name="search" class="filter-input" placeholder="Name or contact..." value="<?= htmlspecialchars($search_query) ?>"></div>
                <div class="filter-group"><label class="filter-label"><i class="fas fa-flag"></i> Status</label><select name="filter_status" class="filter-input"><option value="">All Statuses</option><option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option><option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>Approved</option><option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>Rejected</option></select></div>
                <div class="filter-group"><label class="filter-label"><i class="fas fa-home"></i> Rental</label><select name="filter_rental" class="filter-input"><option value="">All Rentals</option><?php while($rental = $rentals_result->fetch_assoc()): ?><option value="<?= $rental['id'] ?>" <?= $filter_rental == $rental['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rental['title']) ?></option><?php endwhile; ?></select></div>
                <div class="filter-group"><label class="filter-label"><i class="fas fa-calendar"></i> Date From</label><input type="date" name="date_from" class="filter-input" value="<?= htmlspecialchars($date_from) ?>"></div>
                <div class="filter-group"><label class="filter-label"><i class="fas fa-calendar"></i> Date To</label><input type="date" name="date_to" class="filter-input" value="<?= htmlspecialchars($date_to) ?>"></div>
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Apply Filters</button>
                <?php if (!empty($search_query) || !empty($filter_status) || !empty($filter_rental) || !empty($date_from) || !empty($date_to)): ?>
                    <a href="?" class="btn-clear"><i class="fas fa-times"></i> Clear Filters</a>
                <?php endif; ?>
                <button type="button" class="btn-export" onclick="window.print()"><i class="fas fa-file-export"></i> Export/Print</button>
            </div>
        </form>
    </div>
    <div class="table-container">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>#</th><th>Rental</th><th>Tourist Name</th><th>Contact</th><th>Booking Date</th><th>Time</th><th>Place</th><th>Guests/Units</th><th>Price</th><th>Status</th><th>Requested</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if (!empty($temp_results)): ?>
                        <?php $count = 1; foreach($temp_results as $row): ?>
                            <tr>
                                <td data-label="#"><?= $count++ ?></td>
                                <td data-label="Rental"><?= htmlspecialchars($row['rental_title']) ?></td>
                                <td data-label="Tourist"><?= htmlspecialchars($row['tourist_name']) ?></td>
                                <td data-label="Contact"><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td data-label="Booking Date"><?= date("M d, Y", strtotime($row['booking_date'])) ?></td>
                                <td data-label="Time"><?php if (!empty($row['start_time']) && $row['start_time'] != "0000-00-00 00:00:00" && !empty($row['end_time']) && $row['end_time'] != "0000-00-00 00:00:00") { echo date("h:i A", strtotime($row['start_time'])) . " - " . date("h:i A", strtotime($row['end_time'])); } else { echo "N/A"; } ?></td>
                                <td data-label="Place"><?= $row['form_type'] == "rent" ? ($row['place_staying'] ? htmlspecialchars($row['place_staying']) : "Not specified") : "On-site" ?></td>
                                <td data-label="Guests/Units"><?php if ($row['form_type'] == "rent") { echo (!empty($row['units']) && $row['units'] > 0) ? "Units: {$row['units']}" : "N/A"; } else { $details = []; if (!empty($row['guests']) && $row['guests'] > 0) $details[] = "G: {$row['guests']}"; if (!empty($row['units']) && $row['units'] > 0) $details[] = "R: {$row['units']}"; echo !empty($details) ? implode(" | ", $details) : "N/A"; } ?></td>
                                <td data-label="Price"><strong>₱<?= number_format($row['total_price'], 2) ?></strong></td>
                                <td data-label="Status"><form method="post" style="display:inline;"><input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>"><select name="status" class="status-select" onchange="this.form.submit()"><option value="pending" <?= strtolower($row['status']) == 'pending' ? 'selected' : '' ?>>⏳ Pending</option><option value="approved" <?= strtolower($row['status']) == 'approved' ? 'selected' : '' ?>>✓ Approved</option><option value="rejected" <?= strtolower($row['status']) == 'rejected' ? 'selected' : '' ?>>✗ Rejected</option></select><input type="hidden" name="update_status" value="1"></form></td>
                                <td data-label="Requested"><?= date("M d, Y", strtotime($row['date_created'])) ?></td>
                                <td data-label="Action"><a href="?delete_id=<?= $row['booking_id'] ?>" class="btn-delete" onclick="return confirm('Delete this booking?');"><i class="fas fa-trash"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="12"><div class="empty-state"><i class="fas fa-inbox"></i><h3>No Bookings Found</h3><p>No booking requests match your filters.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "user-footer.php"; ?>
<script>
window.addEventListener('load', function() { document.body.classList.add('loaded'); });
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() { alert.style.opacity = '0'; setTimeout(function() { alert.remove(); }, 500); }, 5000);
    });
});
</script>
</body>
</html>