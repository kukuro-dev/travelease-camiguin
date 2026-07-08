<?php
session_start();
include "../db.php"; // Database connection

$error = "";
$success = "";

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
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
<style>
:root {
    --primary-dark: #4e4e4e78;
    --secondary-dark: #2d2d2d;
    --accent-gray: #404040;
    --light-gray: #f5f5f5;
    --border-gray: #e0e0e0;
    --text-dark: #212529;
    --text-muted: #6c757d;
    --success-green: #28a745;
    --danger-red: #dc3545;
}

body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fafafa;
    min-height: 100vh;
}

.page-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(79, 78, 78, 0.23);
}

.page-header h2 {
    font-weight: 700;
    font-size: 2.5rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.page-header i {
    color: #28a745;
}

.form-container {
    max-width: 900px;
    margin: 0 auto 50px;
}

.form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(78, 77, 77, 0.28);
    border: 1px solid var(--border-gray);
    padding: 40px;
}

.alert {
    border-radius: 12px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-danger {
    background: #fee;
    color: #c33;
}

.alert-success {
    background: #efe;
    color: #3a3;
}

.alert::before {
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 1.2rem;
}

.alert-danger::before {
    content: "\f06a";
}

.alert-success::before {
    content: "\f058";
}

.form-section {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.form-label i {
    color: var(--secondary-dark);
    width: 18px;
}

.form-control, .form-select {
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-dark);
    box-shadow: 0 0 0 3px rgba(98, 98, 98, 0.33);
    outline: none;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-input-wrapper input[type=file] {
    position: absolute;
    left: -9999px;
}

.file-input-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px 20px;
    border: 2px dashed var(--border-gray);
    border-radius: 10px;
    background: var(--light-gray);
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.file-input-label:hover {
    border-color: var(--primary-dark);
    background: #fff;
}

.file-input-label i {
    font-size: 2rem;
    color: var(--text-muted);
}

.file-input-label span {
    color: var(--text-dark);
    font-weight: 500;
}

.file-input-label small {
    display: block;
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-top: 5px;
}

.selected-files {
    margin-top: 15px;
    padding: 15px;
    background: var(--light-gray);
    border-radius: 10px;
    display: none;
}

.selected-files.active {
    display: block;
}

.selected-files p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-dark);
    font-weight: 500;
}

.selected-files ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.selected-files li {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.submit-btn {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(26, 26, 26, 0.2);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 26, 26, 0.3);
}

.submit-btn i {
    font-size: 1.1rem;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
}

.category-option {
    display: none;
}

.category-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px 10px;
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.category-label i {
    font-size: 1.8rem;
    color: var(--text-muted);
    margin-bottom: 8px;
}

.category-label span {
    font-size: 0.85rem;
    color: var(--text-dark);
    font-weight: 500;
}

.category-option:checked + .category-label {
    border-color: var(--primary-dark);
    background: var(--light-gray);
}

.category-option:checked + .category-label i {
    color: var(--primary-dark);
}

@media (max-width: 768px) {
    .page-header h2 {
        font-size: 1.8rem;
    }
    
    .form-card {
        padding: 25px 20px;
    }
    
    .category-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>
</head>
<body>

<?php include "user-header.php"; ?>

<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-plus-circle"></i> Add Tourist Spot</h2>
    </div>
</div>

<div class="container form-container">
    <div class="form-card">
        <?php if($error!=""): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if($success!=""): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Spot Name
                </label>
                <input type="text" name="name" class="form-control" placeholder="Enter tourist spot name" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-section">
                        <label class="form-label">
                            <i class="fas fa-city"></i> Barangay
                        </label>
                        <input type="text" name="barangay" class="form-control" placeholder="Enter barangay" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-section">
                        <label class="form-label">
                            <i class="fas fa-map"></i> Municipality
                        </label>
                        <input type="text" name="municipality" class="form-control" placeholder="Enter municipality" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea name="description" class="form-control" rows="4" placeholder="Describe the tourist spot..." required></textarea>
            </div>

            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-dollar-sign"></i> Maintenance / Entrance Fee
                </label>
                <input type="text" name="maintenance_fee" class="form-control" placeholder="e.g., ₱45 per head or ₱700 for 4 persons" required>
            </div>

            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-tags"></i> Category
                </label>
                <select name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <option value="Beach">🏖️ Beach</option>
                    <option value="Falls">💧 Falls</option>
                    <option value="Mountain">⛰️ Mountain</option>
                    <option value="Spring">🌊 Spring</option>
                    <option value="Island">🏝️ Island</option>
                    <option value="Historical">🏛️ Historical</option>
                    <option value="Cultural">🎭 Cultural</option>
                    <option value="Adventure">🎢 Adventure</option>
                    <option value="Park">🌳 Park</option>
                    <option value="Cave">🕳️ Cave</option>
                    <option value="Lagoon">🌅 Lagoon</option>
                    <option value="Other">📍 Other</option>
                </select>
            </div>

            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-images"></i> Tourist Spot Images
                </label>
                <div class="file-input-wrapper">
                    <input type="file" id="spot_images" name="spot_images[]" multiple accept="image/*" required onchange="displayFiles(this)">
                    <label for="spot_images" class="file-input-label">
                        <div>
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>
                                <span>Click to upload images</span>
                                <small>Up to 10 images (JPG, PNG, GIF)</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div id="selected-files" class="selected-files">
                    <p><i class="fas fa-check-circle" style="color: var(--success-green);"></i> Selected Files:</p>
                    <ul id="file-list"></ul>
                </div>
            </div>

            <div class="form-section">
                <button type="submit" name="add_spot" class="submit-btn">
                    <i class="fas fa-plus"></i> Add Tourist Spot
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function displayFiles(input) {
    const fileList = document.getElementById('file-list');
    const selectedFilesDiv = document.getElementById('selected-files');
    
    fileList.innerHTML = '';
    
    if (input.files.length > 0) {
        selectedFilesDiv.classList.add('active');
        for (let i = 0; i < Math.min(input.files.length, 10); i++) {
            const li = document.createElement('li');
            li.textContent = input.files[i].name;
            fileList.appendChild(li);
        }
        
        if (input.files.length > 10) {
            const li = document.createElement('li');
            li.textContent = `(${input.files.length - 10} more files not included - max 10 images)`;
            li.style.color = 'var(--danger-red)';
            fileList.appendChild(li);
        }
    } else {
        selectedFilesDiv.classList.remove('active');
    }
}
</script>

<?php include("user-footer.php"); ?>
</body>
</html>