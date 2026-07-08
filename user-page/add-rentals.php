<?php
session_start();
include "../db.php";

$error = "";
$success = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/user-login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if (isset($_POST['add_rental'])) {
    $category_id = $_POST['category_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $location_address = trim($_POST['location_address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $country = trim($_POST['country']);
    $availability = $_POST['availability'];
    $units = isset($_POST['units']) ? (int)$_POST['units'] : null;
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : null;

    $stmt = $conn->prepare("INSERT INTO rentals 
        (provider_id, category_id, title, description, price, location_address, city, province, country, availability, units, guests, date_created) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("sissdssssiii", $provider_id, $category_id, $title, $description, $price, $location_address, $city, $province, $country, $availability, $units, $guests);

    if ($stmt->execute()) {
        $rental_id = $stmt->insert_id;

        if (isset($_FILES['rental_images'])) {
            $total_files = count($_FILES['rental_images']['name']);
            for ($i = 0; $i < min($total_files, 8); $i++) {
                $filename = $_FILES['rental_images']['name'][$i];
                $tmp_name = $_FILES['rental_images']['tmp_name'][$i];
                $upload_dir = "../uploads/rentals/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $new_filename = uniqid() . "_" . $filename;
                $path = $upload_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $path)) {
                    $stmt_img = $conn->prepare("INSERT INTO rental_images (rental_id, image_path, date_created) VALUES (?, ?, NOW())");
                    $stmt_img->bind_param("is", $rental_id, $path);
                    $stmt_img->execute();

                    if ($i == 0) {
                        $stmt_profile = $conn->prepare("UPDATE rentals SET profile_image=? WHERE id=?");
                        $stmt_profile->bind_param("si", $path, $rental_id);
                        $stmt_profile->execute();
                    }
                }
            }
        }
        $success = "Rental added successfully!";
    } else {
        $error = "Failed to add rental: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Rental - Camiguin Rentals</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background: #fff; min-height: 100vh; opacity: 0; transition: opacity 0.3s; }
body.loaded { opacity: 1; }
.page-wrapper { padding: 20px; max-width: 900px; margin: 0 auto; }
.page-header { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(44,62,80,0.3); }
.page-title { font-size: 28px; font-weight: 700; margin: 0 0 5px 0; display: flex; align-items: center; gap: 12px; }
.page-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
.alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); animation: slideDown 0.3s ease; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
.form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.form-section { margin-bottom: 30px; }
.section-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; display: flex; align-items: center; gap: 10px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: 14px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 8px; }
.form-label i { color: #2c3e50; width: 16px; }
.form-label .required { color: #ef4444; }
.form-input, .form-select, .form-textarea { padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: all 0.2s; font-family: inherit; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #2c3e50; box-shadow: 0 0 0 3px rgba(44,62,80,0.1); }
.form-textarea { resize: vertical; min-height: 120px; }
.form-file { padding: 10px; border: 2px dashed #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: #f9fafb; }
.form-file:hover { border-color: #2c3e50; background: #f3f4f6; }
.file-info { font-size: 12px; color: #6b7280; margin-top: 5px; }
.image-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
.preview-item { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; position: relative; border: 2px solid #e5e7eb; }
.preview-item img { width: 100%; height: 100%; object-fit: cover; }
.preview-badge { position: absolute; top: 5px; right: 5px; background: #2c3e50; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; }
.btn-submit { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform 0.2s, box-shadow 0.2s; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.btn-submit:active { transform: translateY(0); }
.hidden { display: none !important; }
.info-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 600; margin-top: 5px; }

@media (max-width: 768px) {
    .page-wrapper { padding: 15px; }
    .page-header { padding: 20px; }
    .page-title { font-size: 22px; }
    .form-card { padding: 20px; }
    .form-row { grid-template-columns: 1fr; }
    .section-title { font-size: 16px; }
}

@media (max-width: 480px) {
    .page-title { font-size: 20px; }
    .section-title { font-size: 15px; }
}
</style>
</head>
<body>

<?php include "user-header.php"; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            Add New Rental
        </h1>
        <p class="page-subtitle">List your property for tourists to discover</p>
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

    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-card">
            <!-- Basic Information -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-list"></i>
                        Category <span class="required">*</span>
                    </label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?> - <?= htmlspecialchars($cat['description']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-heading"></i>
                        Title <span class="required">*</span>
                    </label>
                    <input type="text" name="title" class="form-input" placeholder="e.g., Beautiful Beach Cottage" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-align-left"></i>
                        Description <span class="required">*</span>
                    </label>
                    <textarea name="description" class="form-textarea" placeholder="Describe your rental property..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-peso-sign"></i>
                        Price (₱) <span class="required">*</span>
                    </label>
                    <input type="number" step="0.01" name="price" class="form-input" placeholder="0.00" required>
                </div>
            </div>

            <!-- Capacity Information -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    Capacity & Availability
                </div>

                <div class="form-row">
                    <div class="form-group hidden" id="units_field">
                        <label class="form-label">
                            <i class="fas fa-cubes"></i>
                            Units Available
                        </label>
                        <input type="number" name="units" class="form-input" min="1" placeholder="e.g., 5">
                        <span class="info-badge">
                            <i class="fas fa-info-circle"></i>
                            Number of units/items available for rent
                        </span>
                    </div>

                    <div class="form-group hidden" id="guests_field">
                        <label class="form-label">
                            <i class="fas fa-bed"></i>
                            Rooms
                        </label>
                        <input type="number" name="guests" class="form-input" min="1" placeholder="e.g., 3">
                        <span class="info-badge">
                            <i class="fas fa-info-circle"></i>
                            Number of rooms available
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-check-circle"></i>
                            Availability Status <span class="required">*</span>
                        </label>
                        <select name="availability" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Location Details
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-map-pin"></i>
                        Location Address <span class="required">*</span>
                    </label>
                    <input type="text" name="location_address" class="form-input" placeholder="e.g., 123 Beach Road" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-city"></i>
                            City <span class="required">*</span>
                        </label>
                        <input type="text" name="city" class="form-input" placeholder="e.g., Mambajao" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map"></i>
                            Province <span class="required">*</span>
                        </label>
                        <input type="text" name="province" class="form-input" placeholder="e.g., Camiguin" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-flag"></i>
                            Country <span class="required">*</span>
                        </label>
                        <input type="text" name="country" class="form-input" placeholder="e.g., Philippines" required>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-images"></i>
                    Property Images
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-camera"></i>
                        Upload Images (Up to 8)
                    </label>
                    <input type="file" name="rental_images[]" id="rental_images" class="form-file" multiple accept="image/*" onchange="previewImages(event)">
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i>
                        First image will be used as the main profile image. Accepts JPG, PNG, GIF.
                    </div>
                    <div id="image_preview" class="image-preview"></div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="add_rental" class="btn-submit">
                <i class="fas fa-plus-circle"></i>
                Add Rental Property
            </button>
        </div>
    </form>
</div>

<?php include "user-footer.php"; ?>

<script>
// Page load animation
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

// Auto-hide alerts
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

// Dynamic fields based on category
document.getElementById("category_id").addEventListener("change", function() {
    var value = this.options[this.selectedIndex].text.toLowerCase();
    var unitsField = document.getElementById("units_field");
    var guestsField = document.getElementById("guests_field");
    
    unitsField.classList.add("hidden");
    guestsField.classList.add("hidden");
    
    if (value.includes("motor") || value.includes("bike") || value.includes("snorkel")) {
        unitsField.classList.remove("hidden");
    }
    if (value.includes("cottage") || value.includes("resort")) {
        guestsField.classList.remove("hidden");
    }
});

// Image preview
function previewImages(event) {
    var preview = document.getElementById('image_preview');
    preview.innerHTML = '';
    
    var files = event.target.files;
    var maxFiles = Math.min(files.length, 8);
    
    for (var i = 0; i < maxFiles; i++) {
        var file = files[i];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var index = preview.children.length;
            var div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            
            if (index === 0) {
                var badge = document.createElement('span');
                badge.className = 'preview-badge';
                badge.textContent = 'Main';
                div.appendChild(badge);
            }
            
            preview.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>