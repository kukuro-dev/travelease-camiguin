<?php
include "../db.php";

// Get search/filter inputs
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// --- Tourist Spots Query ---
$spot_query = "SELECT ts.*, sp.picture_path 
               FROM tourist_spot ts
               LEFT JOIN spot_pictures sp ON ts.spot_id = sp.spot_id
               WHERE 1=1";

if($category != '') $spot_query .= " AND ts.category='". $conn->real_escape_string($category) ."'";
if($search != '') $spot_query .= " AND (ts.name LIKE '%".$conn->real_escape_string($search)."%' 
                                     OR ts.municipality LIKE '%".$conn->real_escape_string($search)."%' 
                                     OR ts.barangay LIKE '%".$conn->real_escape_string($search)."%')";
$spot_query .= " GROUP BY ts.spot_id";

$spot_res = $conn->query($spot_query);

// --- Rentals Query ---
$rent_query = "SELECT r.*, ri.image_path, c.name as category_name
               FROM rentals r
               LEFT JOIN rental_images ri ON r.id = ri.rental_id
               LEFT JOIN categories c ON r.category_id = c.id
               WHERE 1=1";

if($category != '') $rent_query .= " AND c.name='". $conn->real_escape_string($category) ."'";

if($search != '') $rent_query .= " AND (
    r.title LIKE '%".$conn->real_escape_string($search)."%' OR
    r.location_address LIKE '%".$conn->real_escape_string($search)."%' OR
    r.city LIKE '%".$conn->real_escape_string($search)."%' OR
    c.name LIKE '%".$conn->real_escape_string($search)."%'
)";

$rent_query .= " GROUP BY r.id";

$rent_res = $conn->query($rent_query);
?>

<?php include "landing-header.php"; ?>

<style>
/* Page Container */
.explore-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
}

/* Hero Search Section */
.hero-search {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 50px 20px;
    border-radius: 12px;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.hero-search h1 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
}

.hero-search p {
    font-size: 16px;
    color: #ecf0f1;
    margin-bottom: 30px;
    line-height: 1.6;
}

/* Search Form */
.search-form-container {
    max-width: 800px;
    margin: 0 auto;
}

.search-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
}

.search-form input,
.search-form select {
    flex: 1;
    min-width: 200px;
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-form input:focus,
.search-form select:focus {
    border-color: #2c3e50;
    box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
    outline: none;
}

.search-form .btn-search {
    background: #ffffff;
    color: #2c3e50;
    border: none;
    padding: 12px 32px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
}

.search-form .btn-search:hover {
    background: #ecf0f1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255,255,255,0.3);
}

/* Results Counter */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.results-count {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

/* Card Layout */
.listing-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.listing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: #2c3e50;
}

/* Main Image */
.listing-main-image {
    position: relative;
    width: 100%;
    height: 280px;
    overflow: hidden;
    background: #f0f0f0;
}

.listing-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.listing-card:hover .listing-main-image img {
    transform: scale(1.08);
}

.listing-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(44, 62, 80, 0.9);
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* Card Body */
.listing-body {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.listing-title {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    line-height: 1.3;
}

.listing-description {
    font-size: 14px;
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 16px;
    flex-grow: 1;
}

/* Info Items */
.listing-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 16px;
}

.info-item {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #495057;
}

.info-item i {
    width: 20px;
    color: #6c757d;
    margin-right: 8px;
}

.info-item strong {
    color: #2c3e50;
    margin-right: 5px;
}

/* Image Gallery Grid */
.image-gallery {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}

.gallery-thumb {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gallery-thumb:hover {
    transform: scale(1.05);
    border-color: #2c3e50;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Book Button */
.btn-book-now {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
    display: block;
    width: 100%;
}

.btn-book-now:hover {
    background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
    color: #ffffff;
}

/* Modal Styling */
.image-modal .modal-content {
    background: transparent;
    border: none;
}

.image-modal .modal-body {
    padding: 0;
}

.image-modal img {
    border-radius: 8px;
    max-height: 80vh;
    object-fit: contain;
}

.image-modal .btn-close-modal {
    background: rgba(44, 62, 80, 0.9);
    color: #ffffff;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 500;
}

.image-modal .btn-close-modal:hover {
    background: #2c3e50;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #ced4da;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 24px;
    color: #495057;
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 16px;
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-search {
        padding: 35px 15px;
    }
    
    .hero-search h1 {
        font-size: 24px;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-form input,
    .search-form select {
        min-width: 100%;
    }
    
    .listing-main-image {
        height: 220px;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<div class="explore-container">
    <div class="container">

        <!-- Hero Search Section -->
        <div class="hero-search text-center">
            <h1><i class="fas fa-compass me-2"></i> Explore Camiguin</h1>
            <p>Discover beautiful tourist spots and rental services. Browse through beaches, resorts, cottages, and more to plan your perfect trip.</p>
            
            <div class="search-form-container">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-control" placeholder="Search places, rentals..." value="<?= htmlspecialchars($search) ?>">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Beach" <?= ($category=='Beach')?'selected':'' ?>>Beach</option>
                        <option value="Falls" <?= ($category=='Falls')?'selected':'' ?>>Falls</option>
                        <option value="Mountain" <?= ($category=='Mountain')?'selected':'' ?>>Mountain</option>
                        <option value="Spring" <?= ($category=='Spring')?'selected':'' ?>>Spring</option>
                        <option value="Island" <?= ($category=='Island')?'selected':'' ?>>Island</option>
                        <option value="Cottage" <?= ($category=='Cottage')?'selected':'' ?>>Cottage</option>
                        <option value="Resort" <?= ($category=='Resort')?'selected':'' ?>>Resort</option>
                        <option value="Snorkeling" <?= ($category=='Snorkeling')?'selected':'' ?>>Snorkeling</option>
                        <option value="Rent Bike" <?= ($category=='Rent Bike')?'selected':'' ?>>Rent Bike</option>
                        <option value="Motorbike" <?= ($category=='Motorbike')?'selected':'' ?>>Motorbike</option>
                    </select>
                    <button class="btn btn-search" type="submit">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Header -->
        <?php 
        $total_results = ($spot_res ? $spot_res->num_rows : 0) + ($rent_res ? $rent_res->num_rows : 0);
        if($total_results > 0):
        ?>
        <div class="results-header">
            <div class="results-count">
                <i class="fas fa-map-marker-alt me-2"></i> 
                <?= $total_results ?> <?= $total_results == 1 ? 'Result' : 'Results' ?> Found
            </div>
        </div>
        <?php endif; ?>

        <!-- Listings Grid -->
        <div class="row">
            <!-- Tourist Spots -->
            <?php if($spot_res && $spot_res->num_rows > 0): ?>
                <?php while($spot = $spot_res->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="listing-card">
                            <div class="listing-main-image">
                                <img src="<?= htmlspecialchars($spot['picture_path'] ?? '../image/default.png') ?>" alt="<?= htmlspecialchars($spot['name']) ?>">
                                <span class="listing-badge"><i class="fas fa-mountain me-1"></i> Tourist Spot</span>
                            </div>
                            
                            <div class="listing-body">
                                <h5 class="listing-title"><?= htmlspecialchars($spot['name']) ?></h5>
                                <p class="listing-description"><?= htmlspecialchars($spot['description']) ?></p>
                                
                                <div class="listing-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($spot['barangay'] . ', ' . $spot['municipality']) ?></span>
                                    </div>
                                </div>

                                <!-- Image Gallery -->
                                <?php
                                $spot_id = $spot['spot_id'];
                                $images_res = $conn->query("SELECT picture_path FROM spot_pictures WHERE spot_id='$spot_id' LIMIT 6");
                                if($images_res && $images_res->num_rows > 0):
                                ?>
                                <div class="image-gallery">
                                    <?php while($img = $images_res->fetch_assoc()): ?>
                                        <img src="<?= htmlspecialchars($img['picture_path']) ?>" 
                                             class="gallery-thumb" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imgModal" 
                                             data-img="<?= htmlspecialchars($img['picture_path']) ?>"
                                             alt="Gallery image">
                                    <?php endwhile; ?>
                                </div>
                                <?php endif; ?>

                                <a href="user-login.php" class="btn-book-now">
                                    <i class="fas fa-info-circle me-2"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <!-- Rentals -->
            <?php if($rent_res && $rent_res->num_rows > 0): ?>
                <?php while($rent = $rent_res->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="listing-card">
                            <div class="listing-main-image">
                                <img src="<?= htmlspecialchars($rent['profile_image'] ?? '../image/default.png') ?>" alt="<?= htmlspecialchars($rent['title']) ?>">
                                <span class="listing-badge"><i class="fas fa-key me-1"></i> Rental</span>
                            </div>
                            
                            <div class="listing-body">
                                <h5 class="listing-title"><?= htmlspecialchars($rent['title']) ?></h5>
                                <p class="listing-description"><?= htmlspecialchars($rent['description']) ?></p>
                                
                                <div class="listing-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($rent['location_address'] . ', ' . $rent['city']) ?></span>
                                    </div>
                                </div>

                                <!-- Image Gallery -->
                                <?php
                                $rent_id = $rent['id'];
                                $images_res = $conn->query("SELECT image_path FROM rental_images WHERE rental_id='$rent_id' LIMIT 6");
                                if($images_res && $images_res->num_rows > 0):
                                ?>
                                <div class="image-gallery">
                                    <?php while($img = $images_res->fetch_assoc()): ?>
                                        <img src="<?= htmlspecialchars($img['image_path']) ?>" 
                                             class="gallery-thumb" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imgModal" 
                                             data-img="<?= htmlspecialchars($img['image_path']) ?>"
                                             alt="Gallery image">
                                    <?php endwhile; ?>
                                </div>
                                <?php endif; ?>

                                <a href="user-login.php" class="btn-book-now">
                                    <i class="fas fa-calendar-check me-2"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Empty State -->
        <?php if($total_results == 0): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No Results Found</h3>
            <p>Try adjusting your search or filter criteria</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Image Modal -->
<div class="modal fade image-modal" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <img src="" id="modalImage" class="w-100" alt="Preview">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalImage = document.getElementById('modalImage');
    var imgThumbs = document.querySelectorAll('.gallery-thumb');

    imgThumbs.forEach(function(img){
        img.addEventListener('click', function(){
            modalImage.src = this.dataset.img;
        });
    });
});
</script>

<?php include "landing-footer.php"; ?>