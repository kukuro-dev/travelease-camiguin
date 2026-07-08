<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Handle search/filter
$filter_category = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM tourist_spot WHERE admin_id='$admin_id'";
if($filter_category) $sql .= " AND category='$filter_category'";
if($search_query) $sql .= " AND name LIKE '%$search_query%'";
$sql .= " ORDER BY spot_id DESC";

$spots_res = $conn->query($sql);

// Categories list
$categories = ['Beach','Falls','Mountain','Spring','Island','Historical','Cultural','Adventure','Park','Cave','Other'];
?>

<?php include "admin-header.php"; ?>

<div class="container mt-5">
    <h2 class="mb-4">Tourist Spots Overview</h2>

    <!-- Search & Filter -->
    <form class="row g-3 mb-4" method="get">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $filter_category==$cat?'selected':'' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
        </div>
    </form>

    <div class="row">
        <?php while($spot = $spots_res->fetch_assoc()): ?>
            <?php
                // Fetch all images for this spot
                $images_res = $conn->query("SELECT * FROM spot_pictures WHERE spot_id='".$spot['spot_id']."' ORDER BY uploaded_at ASC");
                $images = [];
                while($img = $images_res->fetch_assoc()) $images[] = $img;
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <?php if(count($images) > 0): ?>
                        <img src="<?= $images[0]['picture_path'] ?>" class="card-img-top zoomable" style="height:200px; object-fit:cover; cursor:pointer;" onclick="zoomImage(this)">
                    <?php else: ?>
                        <div style="height:200px; background:#ccc; display:flex; align-items:center; justify-content:center; color:#555;">No Image</div>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($spot['name']) ?></h5>
                        <p><strong>Category:</strong> <?= htmlspecialchars($spot['category']) ?></p>
                        <p class="card-text"><strong>Location:</strong> <?= htmlspecialchars($spot['barangay']) ?>, <?= htmlspecialchars($spot['municipality']) ?></p>
                        <p class="card-text"><strong>Description:</strong> <?= htmlspecialchars($spot['description']) ?></p>
                        <p class="card-text"><strong>Fee:</strong> <?= htmlspecialchars($spot['maintenance_fee']) ?></p>

                        <?php if(count($images) > 1): ?>
                            <div class="d-flex flex-wrap mt-2">
                                <?php foreach(array_slice($images, 1) as $img): ?>
                                    <img src="<?= $img['picture_path'] ?>" style="width:70px; height:70px; object-fit:cover; border-radius:4px; margin-right:5px; margin-bottom:5px; cursor:pointer;" onclick="zoomImage(this)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Image Zoom Modal -->
<div id="zoomModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); justify-content:center; align-items:center; z-index:1050;">
    <span style="position:absolute; top:20px; right:30px; font-size:30px; color:#fff; cursor:pointer;" onclick="closeZoom()">&times;</span>
    <img id="zoomImg" style="max-width:90%; max-height:90%; border-radius:10px;">
</div>

<script>
function zoomImage(img){
    document.getElementById('zoomImg').src = img.src;
    document.getElementById('zoomModal').style.display = 'flex';
}
function closeZoom(){
    document.getElementById('zoomModal').style.display = 'none';
}
</script>

<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("admin-footer.php"); ?>
