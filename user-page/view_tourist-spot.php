<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$error = "";
$success = "";

// --- Update Tourist Spot Info ---
if (isset($_POST['update_spot']) && isset($_POST['spot_id'])) {
    $spot_id = $_POST['spot_id'];
    $name = trim($_POST['name']);
    $barangay = trim($_POST['barangay']);
    $municipality = trim($_POST['municipality']);
    $description = trim($_POST['description']);
    $maintenance_fee = trim($_POST['maintenance_fee']);
    $category = $_POST['category'] ?? '';

    $stmt = $conn->prepare("UPDATE tourist_spot SET name=?, barangay=?, municipality=?, description=?, maintenance_fee=?, category=? WHERE spot_id=? AND admin_id=?");
    $stmt->bind_param("ssssssss", $name, $barangay, $municipality, $description, $maintenance_fee, $category, $spot_id, $admin_id);

    if ($stmt->execute()) {
        $success = "Tourist spot info updated successfully!";
    } else {
        $error = "Failed to update tourist spot info.";
    }
}

// --- Add Additional Images ---
if (isset($_POST['upload_images']) && isset($_POST['spot_id'])) {
    $spot_id = $_POST['spot_id'];
    $total_files = count($_FILES['spot_images']['name']);
    $allowed_types = ['image/jpeg','image/jpg','image/png','image/gif'];

    $existing_images_res = $conn->query("SELECT COUNT(*) AS total FROM spot_pictures WHERE spot_id='$spot_id'");
    $existing_images = $existing_images_res->fetch_assoc();

    if ($existing_images['total'] + $total_files > 10) {
        $error = "You can only have up to 10 images per tourist spot.";
    } else {
        $upload_dir = "../uploads/tourist_spots/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        for ($i = 0; $i < $total_files; $i++) {
            $filename = $_FILES['spot_images']['name'][$i];
            $tmp_name = $_FILES['spot_images']['tmp_name'][$i];
            $file_type = mime_content_type($tmp_name);
            if (!in_array($file_type, $allowed_types)) continue;

            $path = $upload_dir . time() . "_" . basename($filename);

            if (move_uploaded_file($tmp_name, $path)) {
                $last_pic = $conn->query("SELECT picture_id FROM spot_pictures ORDER BY picture_id DESC LIMIT 1");
                $row_pic = $last_pic->fetch_assoc();
                $last_pic_id = $row_pic ? intval(substr($row_pic['picture_id'], 6)) : 0;
                $picture_id = "TSPID" . str_pad($last_pic_id + 1, 6, "0", STR_PAD_LEFT);

                $stmt_img = $conn->prepare("INSERT INTO spot_pictures (picture_id, spot_id, picture_path, uploaded_at) VALUES (?, ?, ?, NOW())");
                $stmt_img->bind_param("sss", $picture_id, $spot_id, $path);
                $stmt_img->execute();
            }
        }
        $success = "Images uploaded successfully!";
    }
}

// --- Remove Image ---
if (isset($_GET['delete_image'])) {
    $img_id = $_GET['delete_image'];
    $img_res = $conn->query("SELECT * FROM spot_pictures WHERE picture_id='$img_id'");
    if ($img_res->num_rows > 0) {
        $img_row = $img_res->fetch_assoc();
        if (file_exists($img_row['picture_path'])) unlink($img_row['picture_path']);
        $conn->query("DELETE FROM spot_pictures WHERE picture_id='$img_id'");
        $success = "Image removed successfully!";
    }
}

// --- Fetch Tourist Spots ---
$spots_res = $conn->query("SELECT * FROM tourist_spot WHERE admin_id='$admin_id' ORDER BY spot_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Tourist Spots - Camiguin Rentals</title>
<link rel="icon" href="../image/easeico.ico" type="image/x-icon">
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding-top: 70px; }
.card { box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 10px; padding: 20px; margin-bottom: 15px; transition: transform 0.2s; }
.card:hover { transform: translateY(-5px); }
</style>
</head>
<body>

<?php include "user-header.php"; ?>

<div class="container mt-5">
    <h2 class="mb-4">My Tourist Spots</h2>

    <?php if($error!=""): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success!=""): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php while($spot = $spots_res->fetch_assoc()): ?>
        <!-- Tourist Spot Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Edit Tourist Spot Info</h5>
                <form action="" method="post">
                    <input type="hidden" name="spot_id" value="<?= $spot['spot_id'] ?>">
                    <div class="mb-2"><label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($spot['name']) ?>" required>
                    </div>
                    <div class="mb-2"><label>Barangay</label>
                        <input type="text" name="barangay" class="form-control" value="<?= htmlspecialchars($spot['barangay']) ?>" required>
                    </div>
                    <div class="mb-2"><label>Municipality</label>
                        <input type="text" name="municipality" class="form-control" value="<?= htmlspecialchars($spot['municipality']) ?>" required>
                    </div>
                    <div class="mb-2"><label>Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($spot['description']) ?></textarea>
                    </div>
                    <div class="mb-2"><label>Maintenance / Entrance Fee</label>
                        <input type="text" name="maintenance_fee" class="form-control" value="<?= htmlspecialchars($spot['maintenance_fee']) ?>" placeholder="e.g., 45 per head or 700 for 4 persons" required>
                    </div>
                    <div class="mb-2"><label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $categories = ['Beach','Falls','Mountain','Spring','Island','Historical','Cultural','Adventure','Park','Cave','Other'];
                            foreach($categories as $cat){
                                $selected = ($spot['category'] == $cat) ? "selected" : "";
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" name="update_spot" class="btn btn-success mt-2"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Additional Images -->
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-images"></i> Tourist Spot Images</h5>
                <?php
                $images_res = $conn->query("SELECT * FROM spot_pictures WHERE spot_id='".$spot['spot_id']."' ORDER BY uploaded_at ASC");
                $images = [];
                while($img = $images_res->fetch_assoc()) $images[] = $img;
                ?>

                <?php if(count($images) > 0): ?>
                    <div class="d-flex flex-wrap mb-2">
                        <?php foreach($images as $img): ?>
                            <div class="position-relative m-1">
                                <img src="<?= $img['picture_path'] ?>" style="width:100px; height:100px; object-fit:cover; border-radius:4px;">
                                <a href="?delete_image=<?= $img['picture_id'] ?>" style="position:absolute;top:0;right:0;color:red;background:#fff;border-radius:50%;padding:2px 5px;text-decoration:none;"><i class="fas fa-times"></i></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images uploaded yet.</p>
                <?php endif; ?>

                <?php
                $total_uploaded = count($images);
                if($total_uploaded < 10): ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="spot_id" value="<?= $spot['spot_id'] ?>">
                        <div class="mb-2">
                            <label>Add Images (Max <?= 10 - $total_uploaded ?> more)</label>
                            <input type="file" name="spot_images[]" multiple accept=".jpg,.jpeg,.png,.gif" class="form-control">
                        </div>
                        <button type="submit" name="upload_images" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Images</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("user-footer.php"); ?>
</body>
</html>
