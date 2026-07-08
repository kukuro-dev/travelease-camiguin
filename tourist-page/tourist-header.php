<?php
// DB connection only
include_once __DIR__ . "/../db.php";

// Fetch categories for navbar dropdown
$catSql = "SELECT * FROM categories ORDER BY name ASC";
$catResult = $conn->query($catSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 

<style>
/* Navbar Styling */
.main-navbar {
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 0.3rem 1rem;
}

.navbar-brand img { 
    height: 70px;
    transition: transform 0.3s ease;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

.navbar-nav .nav-link {
    font-weight: 500;
    color: #495057;
    padding: 8px 15px;
    font-size: 14.5px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link i {
    margin-right: 5px;
    color: #6c757d;
}

.navbar-nav .nav-link:hover {
    color: #2c3e50;
    background: #f8f9fa;
}

.navbar-nav .nav-item.dropdown:hover > .nav-link {
    color: #2c3e50;
}

.dropdown-menu {
    min-width: 200px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    padding: 5px 0;
}

.dropdown-item {
    padding: 8px 20px;
    font-size: 14px;
    color: #495057;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #2c3e50;
    padding-left: 25px;
}

.btn-search {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-search:hover {
    background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
    transform: translateY(-1px);
}

.search-bar {
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 991px) {
    .navbar-brand img {
        height: 55px;
    }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg main-navbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    
    <!-- Brand -->
    <a class="navbar-brand" href="../tourist-dashboard.php">
      <img src="../image/logo.png" alt="Camiguin Rentals">
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Links -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="../tourist-page/tourist-dashboard.php">
            <i class="fas fa-home"></i> Home
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="tourist-account.php">
            <i class="fas fa-user"></i> My Account
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-radar"></i> Explore More
          </a>
          <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
            <?php if ($catResult && $catResult->num_rows > 0): ?>
              <?php while($row = $catResult->fetch_assoc()): ?>
                <li>
                  <a class="dropdown-item" href="explore.php?category_id=<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['name']) ?>
                  </a>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li><span class="dropdown-item text-muted">No categories available</span></li>
            <?php endif; ?>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="booking.php">
            <i class="fas fa-calendar-check"></i> Bookings
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="reviews.php">
            <i class="fas fa-star"></i> Reviews
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="tourist-message.php">
            <i class="fas fa-envelope"></i> Messages
          </a>
        </li>

        <li class="nav-item nav-logout">
          <a class="nav-link" href="../users/landing-page.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
      </ul>

      <!-- Search bar -->
      <div class="d-flex ms-lg-3 mt-2 mt-lg-0">
        <form class="d-flex" action="explore.php" method="get">
          <input class="form-control me-2 search-bar" type="search" name="query" placeholder="Search rentals or spots..." aria-label="Search">
          <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
        </form>
      </div>

    </div>
  </div>
</nav>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>
</body>
</html>
