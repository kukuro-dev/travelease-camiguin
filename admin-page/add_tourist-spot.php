<?php
session_start();
include "../db.php"; // Database connection

$error = "";
$success = "";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'];

// Handle form submission
if (isset($_POST['add_spot'])) {
    $name = trim($_POST['name']);
    $barangay = trim($_POST['barangay']);
    $municipality = trim($_POST['municipality']);
    $description = trim($_POST['description']);
    $maintenance_fee = trim($_POST['maintenance_fee']);
    $category = $_POST['category'] ?? '';

    // Make sure admin_id exists in admin table
    $check_admin = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $check_admin->bind_param("s", $admin_id);
    $check_admin->execute();
    $admin_result = $check_admin->get_result();

    if ($admin_result->num_rows === 0) {
        $error = "Invalid admin. Cannot add tourist spot.";
    } else {
        // Generate spot_id
        $last = $conn->query("SELECT spot_id FROM tourist_spot ORDER BY spot_id DESC LIMIT 1");
        $row = $last->fetch_assoc();
        $last_id = $row ? intval(substr($row['spot_id'], 4)) : 0;
        $spot_id = "TSID" . str_pad($last_id + 1, 5, "0", STR_PAD_LEFT);

        // Insert tourist spot
        $stmt = $conn->prepare("INSERT INTO tourist_spot (spot_id, name, barangay, municipality, description, maintenance_fee, category, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $spot_id, $name, $barangay, $municipality, $description, $maintenance_fee, $category, $admin_id);

        if ($stmt->execute()) {
            $success = "Tourist spot added successfully!";
            $spot_id_inserted = $spot_id;

            // Handle multiple images (up to 10)
            if (isset($_FILES['spot_images'])) {
                $total_files = count($_FILES['spot_images']['name']);
                for ($i = 0; $i < min($total_files, 10); $i++) {
                    $filename = $_FILES['spot_images']['name'][$i];
                    $tmp_name = $_FILES['spot_images']['tmp_name'][$i];

                    // Choose upload folder
                    $upload_dir = "../uploads/tourist_spots/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                    $new_filename = uniqid() . "_" . $filename;
                    $path = $upload_dir . $new_filename;

                    if (move_uploaded_file($tmp_name, $path)) {
                        // Generate picture_id
                        $last_pic = $conn->query("SELECT picture_id FROM spot_pictures ORDER BY picture_id DESC LIMIT 1");
                        $row_pic = $last_pic->fetch_assoc();
                        $last_pic_id = $row_pic ? intval(substr($row_pic['picture_id'], 6)) : 0;
                        $picture_id = "TSPID" . str_pad($last_pic_id + 1, 6, "0", STR_PAD_LEFT);

                        $stmt_img = $conn->prepare("INSERT INTO spot_pictures (picture_id, spot_id, picture_path, uploaded_at) VALUES (?, ?, ?, NOW())");
                        $stmt_img->bind_param("sss", $picture_id, $spot_id_inserted, $path);
                        $stmt_img->execute();
                    }
                }
            }
        } else {
            $error = "Failed to add tourist spot: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Tourist Spot - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f4f6f9; }
.container { max-width: 900px; margin:50px auto; }
.card { padding: 20px; border-radius: 12px; box-shadow:0 0 20px rgba(0,0,0,0.1); background:#fff; }
input, textarea, select { padding: 10px; border-radius: 6px; border:1px solid #ccc; }
input[type=file] { padding:3px; }
button { width:100%; padding:10px; border:none; border-radius:6px; }
</style>
</head>
<body>

<?php include "admin-header.php"; ?>

<div class="container">
    <div class="card">
        <h3 class="mb-3"><i class="fas fa-plus-circle"></i> Add Tourist Spot</h3>

        <?php if($error!=""): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if($success!=""): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label><i class="fas fa-map-marker-alt"></i> Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-city"></i> Barangay</label>
                <input type="text" name="barangay" class="form-control" required>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-map"></i> Municipality</label>
                <input type="text" name="municipality" class="form-control" required>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-dollar-sign"></i> Maintenance / Entrance Fee</label>
                <input type="text" name="maintenance_fee" class="form-control" placeholder="e.g., 45 per head or 700 for 4 persons" required>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-tags"></i> Category</label>
                <select name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <option value="Beach">Beach</option>
                    <option value="Falls">Falls</option>
                    <option value="Mountain">Mountain</option>
                    <option value="Spring">Spring</option>
                    <option value="Island">Island</option>
                    <option value="Historical">Historical</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Adventure">Adventure</option>
                    <option value="Park">Park</option>
                    <option value="Cave">Cave</option>
                    <option value="Cave">Lagoon</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label><i class="fas fa-images"></i> Tourist Spot Images (up to 10)</label>
                <input type="file" name="spot_images[]" multiple accept="image/*" required>
            </div>
            <div class="mb-3">
                <button type="submit" name="add_spot" class="btn btn-success"><i class="fas fa-plus"></i> Add Tourist Spot</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("admin-footer.php"); ?>
</body>
</html>
