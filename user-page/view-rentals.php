<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/user-login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Update Rental Info + Profile Image
if (isset($_POST['update_rental']) && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $location_address = trim($_POST['location_address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $country = trim($_POST['country']);
    $availability = $_POST['availability'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
    $guests   = isset($_POST['guests']) ? intval($_POST['guests']) : 0;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != '') {
        $file = $_FILES['profile_image'];
        $upload_dir = "../uploads/rentals/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $profile_image_path = $upload_dir . time() . "_" . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $profile_image_path);
        $stmt = $conn->prepare("UPDATE rentals SET title=?, description=?, price=?, location_address=?, city=?, province=?, country=?, availability=?, profile_image=?, quantity=?, capacity=?, guests=? WHERE id=? AND provider_id=?");
        $stmt->bind_param("ssdssssssiiiii", $title, $description, $price, $location_address, $city, $province, $country, $availability, $profile_image_path, $quantity, $capacity, $guests, $rental_id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE rentals SET title=?, description=?, price=?, location_address=?, city=?, province=?, country=?, availability=?, quantity=?, capacity=?, guests=? WHERE id=? AND provider_id=?");
        $stmt->bind_param("ssdssssssiiii", $title, $description, $price, $location_address, $city, $province, $country, $availability, $quantity, $capacity, $guests, $rental_id, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Rental info updated successfully!";
    } else {
        $error = "Failed to update rental info.";
    }
}

// Add Additional Images
if (isset($_POST['upload_images']) && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];
    $total_files = count($_FILES['rental_images']['name']);
    $existing_images_res = $conn->query("SELECT COUNT(*) AS total FROM rental_images WHERE rental_id='$rental_id'");
    $existing_images = $existing_images_res->fetch_assoc();
    $rental_res = $conn->query("SELECT profile_image FROM rentals WHERE id='$rental_id'");
    $rental_data = $rental_res->fetch_assoc();
    $profile_count = $rental_data['profile_image'] ? 1 : 0;

    if ($existing_images['total'] + $profile_count + $total_files > 10) {
        $error = "You can only have up to 10 images per rental (including profile).";
    } else {
        $upload_dir = "../uploads/rentals/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        for ($i = 0; $i < $total_files; $i++) {
            $filename = $_FILES['rental_images']['name'][$i];
            $tmp_name = $_FILES['rental_images']['tmp_name'][$i];
            $path = $upload_dir . time() . "_" . $filename;
            if (move_uploaded_file($tmp_name, $path)) {
                $stmt = $conn->prepare("INSERT INTO rental_images (rental_id, image_path, date_created) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $rental_id, $path);
                $stmt->execute();
            }
        }
        $success = "Images uploaded successfully!";
    }
}

// Remove Image
if (isset($_GET['delete_image'])) {
    $img_id = $_GET['delete_image'];
    $img_res = $conn->query("SELECT * FROM rental_images WHERE id='$img_id'");
    if ($img_res->num_rows > 0) {
        $img_row = $img_res->fetch_assoc();
        if (file_exists($img_row['image_path'])) unlink($img_row['image_path']);
        $conn->query("DELETE FROM rental_images WHERE id='$img_id'");
        $success = "Image removed successfully!";
    }
}

$rentals_res = $conn->query("SELECT * FROM rentals WHERE provider_id='$user_id' ORDER BY date_created DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Rentals</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background: #fff; min-height: 100vh; opacity: 0; transition: opacity 0.3s; }
body.loaded { opacity: 1; }
.page-wrapper { padding: 20px; max-width: 1400px; margin: 0 auto; }
.page-header { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(44,62,80,0.3); }
.page-title { font-size: 28px; font-weight: 700; margin: 0 0 5px 0; display: flex; align-items: center; gap: 12px; }
.page-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
.alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); animation: slideDown 0.3s ease; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
.rental-card { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.rental-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb; }
.rental-title { font-size: 20px; font-weight: 700; color: #2c3e50; display: flex; align-items: center; gap: 10px; }
.section-divider { margin: 25px 0; padding: 15px 0; border-top: 2px solid #e5e7eb; }
.section-title { font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
.profile-image-section { text-align: center; margin-bottom: 20px; }
.profile-image-wrapper { display: inline-block; position: relative; }
.profile-image { width: 180px; height: 180px; object-fit: cover; border-radius: 12px; border: 3px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.image-placeholder { width: 180px; height: 180px; background: linear-gradient(135deg, #e5e7eb, #cbd5e0); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 48px; color: #9ca3af; }
.file-upload-btn { display: inline-block; margin-top: 10px; padding: 8px 16px; background: #2c3e50; color: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.2s; }
.file-upload-btn:hover { background: #1a252f; }
.file-upload-btn input { display: none; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: 13px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 6px; }
.form-label i { color: #2c3e50; width: 14px; }
.form-input, .form-select, .form-textarea { padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; font-family: inherit; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #2c3e50; }
.form-textarea { resize: vertical; min-height: 100px; }
.btn-save { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s; }
.btn-save:hover { transform: translateY(-2px); }
.image-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-bottom: 15px; }
.gallery-item { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 1; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.gallery-image { width: 100%; height: 100%; object-fit: cover; }
.delete-image-btn { position: absolute; top: 5px; right: 5px; background: rgba(239,68,68,0.95); color: white; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: background 0.2s; }
.delete-image-btn:hover { background: #dc2626; }
.empty-gallery { text-align: center; padding: 40px 20px; color: #9ca3af; }
.empty-gallery i { font-size: 48px; margin-bottom: 10px; display: block; }
.upload-section { background: #f9fafb; padding: 20px; border-radius: 10px; border: 2px dashed #e5e7eb; }
.file-input-custom { display: none; }
.file-label { display: inline-block; padding: 10px 20px; background: #2c3e50; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background 0.2s; }
.file-label:hover { background: #1a252f; }
.btn-upload { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-top: 10px; transition: transform 0.2s; }
.btn-upload:hover { transform: translateY(-2px); }
.info-badge { display: inline-block; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 600; }
.empty-state { text-align: center; padding: 80px 20px; }
.empty-state i { font-size: 64px; color: #cbd5e0; margin-bottom: 20px; }
.empty-state h3 { color: #4a5568; margin-bottom: 10px; }
.empty-state p { color: #718096; margin-bottom: 20px; }
.btn-add { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s; }
.btn-add:hover { transform: translateY(-2px); }

@media (max-width: 768px) {
    .page-wrapper { padding: 15px; }
    .page-header { padding: 20px; }
    .page-title { font-size: 22px; }
    .rental-card { padding: 20px; }
    .form-grid { grid-template-columns: 1fr; }
    .rental-header { flex-direction: column; align-items: flex-start; gap: 15px; }
    .image-gallery { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
    .profile-image, .image-placeholder { width: 150px; height: 150px; }
}
</style>
</head>
<body>
<?php include "user-header.php"; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-home"></i>
            My Rentals
        </h1>
        <p class="page-subtitle">Manage your rental properties and images</p>
    </div>

    <?php if($error!=""): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if($success!=""): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if($rentals_res->num_rows > 0): ?>
        <?php while($rental = $rentals_res->fetch_assoc()): ?>
            <div class="rental-card">
                <!-- Edit Rental Info -->
                <div class="rental-header">
                    <div class="rental-title">
                        <i class="fas fa-edit"></i>
                        Edit Rental Information
                    </div>
                </div>

                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">

                    <!-- Profile Image -->
                    <div class="profile-image-section">
                        <div class="profile-image-wrapper">
                            <?php if($rental['profile_image']): ?>
                                <img src="<?= $rental['profile_image'] ?>" class="profile-image" alt="Profile">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <br>
                        <label class="file-upload-btn">
                            <i class="fas fa-camera"></i> Change Profile Image
                            <input type="file" name="profile_image" accept="image/*">
                        </label>
                    </div>

                    <!-- Basic Info -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-heading"></i>
                                Title
                            </label>
                            <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($rental['title']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-peso-sign"></i>
                                Price
                            </label>
                            <input type="number" step="0.01" name="price" class="form-input" value="<?= $rental['price'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle"></i>
                                Availability
                            </label>
                            <select name="availability" class="form-select">
                                <option value="Available" <?= $rental['availability']=='Available'?'selected':'' ?>>Available</option>
                                <option value="Unavailable" <?= $rental['availability']=='Unavailable'?'selected':'' ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>
                            Description
                        </label>
                        <textarea name="description" class="form-textarea"><?= htmlspecialchars($rental['description']) ?></textarea>
                    </div>

                    <!-- Location Info -->
                    <div class="section-divider"></div>
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Location Details
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-pin"></i>
                                Address
                            </label>
                            <input type="text" name="location_address" class="form-input" value="<?= htmlspecialchars($rental['location_address']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-city"></i>
                                City
                            </label>
                            <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($rental['city']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map"></i>
                                Province
                            </label>
                            <input type="text" name="province" class="form-input" value="<?= htmlspecialchars($rental['province']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-flag"></i>
                                Country
                            </label>
                            <input type="text" name="country" class="form-input" value="<?= htmlspecialchars($rental['country']) ?>" required>
                        </div>
                    </div>

                    <!-- Capacity Info -->
                    <?php
                    $cat_id = $rental['category_id'];
                    if (in_array($cat_id, [3,4,5])): 
                    ?>
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="fas fa-cubes"></i>
                            Capacity Information
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-boxes"></i>
                                Quantity Available
                            </label>
                            <input type="number" name="quantity" class="form-input" value="<?= $rental['quantity'] ?>" min="0">
                        </div>
                    <?php elseif (in_array($cat_id, [1,2,6])): ?>
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            Capacity Information
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-bed"></i>
                                    Available Rooms
                                </label>
                                <input type="number" name="capacity" class="form-input" value="<?= $rental['capacity'] ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-friends"></i>
                                    Guest Capacity
                                </label>
                                <input type="number" name="guests" class="form-input" value="<?= $rental['guests'] ?>" min="1">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 25px;">
                        <button type="submit" name="update_rental" class="btn-save">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>

                <!-- Image Gallery -->
                <div class="section-divider"></div>
                <div class="section-title">
                    <i class="fas fa-images"></i>
                    Rental Images Gallery
                    <span class="info-badge">
                        <?php
                        $additional_images_res = $conn->query("SELECT * FROM rental_images WHERE rental_id='".$rental['id']."' ORDER BY date_created ASC");
                        $additional_images = [];
                        while($img = $additional_images_res->fetch_assoc()) $additional_images[] = $img;
                        $total_uploaded = count($additional_images) + ($rental['profile_image'] ? 1 : 0);
                        echo $total_uploaded . "/10 images";
                        ?>
                    </span>
                </div>

                <?php if(count($additional_images) > 0): ?>
                    <div class="image-gallery">
                        <?php foreach($additional_images as $img): ?>
                            <div class="gallery-item">
                                <img src="<?= $img['image_path'] ?>" class="gallery-image" alt="Rental">
                                <a href="?delete_image=<?= $img['id'] ?>" class="delete-image-btn" onclick="return confirm('Delete this image?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-gallery">
                        <i class="fas fa-image"></i>
                        <p>No additional images uploaded yet</p>
                    </div>
                <?php endif; ?>

                <?php if($total_uploaded < 10): ?>
                    <div class="upload-section">
                        <form action="" method="post" enctype="multipart/form-data" id="upload-form-<?= $rental['id'] ?>">
                            <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">
                            <label class="file-label" for="file-<?= $rental['id'] ?>">
                                <i class="fas fa-plus"></i> Choose Images
                            </label>
                            <input type="file" id="file-<?= $rental['id'] ?>" name="rental_images[]" class="file-input-custom" multiple accept="image/*">
                            <span style="margin-left: 10px; font-size: 13px; color: #6b7280;">
                                Max <?= 10 - $total_uploaded ?> more images
                            </span>
                            <button type="submit" name="upload_images" class="btn-upload">
                                <i class="fas fa-upload"></i>
                                Upload Images
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="info-badge" style="display: block; text-align: center;">
                        <i class="fas fa-info-circle"></i>
                        Maximum 10 images reached
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-home"></i>
            <h3>No Rentals Yet</h3>
            <p>You haven't added any rental properties yet.</p>
            <a href="add-rental.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Add Your First Rental
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include "user-footer.php"; ?>

<script>
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

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