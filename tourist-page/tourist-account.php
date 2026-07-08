<?php
session_start();
include "../db.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'tourist') {
    header("Location: ../users/user-login.php");
    exit;
}

$error = "";
$success = "";

// Fetch session data
$user_id       = $_SESSION['user_id'];
$user_code     = $_SESSION['user_code'];
$fullname      = $_SESSION['fullname'];
$email         = $_SESSION['email'];
$user_type     = $_SESSION['user_type'];
$contact       = $_SESSION['contact_number'];
$address       = $_SESSION['address'];
$city          = $_SESSION['city'];
$province      = $_SESSION['province'];
$country       = $_SESSION['country'];

// Handle form submission
if (isset($_POST['update'])) {
    $fullname = trim($_POST['fullname']);
    $contact = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $country = trim($_POST['country']);

    $stmt = $conn->prepare("UPDATE users SET fullname=?, contact_number=?, address=?, city=?, province=?, country=? WHERE user_id=?");
    $stmt->bind_param("sssssss", $fullname, $contact, $address, $city, $province, $country, $user_id);

    if ($stmt->execute()) {
        $success = "Account information updated successfully!";
        // Update session variables
        $_SESSION['fullname'] = $fullname;
        $_SESSION['contact_number'] = $contact;
        $_SESSION['address'] = $address;
        $_SESSION['city'] = $city;
        $_SESSION['province'] = $province;
        $_SESSION['country'] = $country;
    } else {
        $error = "Failed to update account. Please try again.";
    }
}
?>

<?php include "tourist-header.php"; ?>

<style>
    .account-container {
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
        padding: 40px 0;
    }
    
    .account-card {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .account-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ffffff;
        padding: 30px;
        border-bottom: 3px solid #1a252f;
    }
    
    .account-header h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .account-body {
        padding: 40px;
    }
    
    .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .form-label i {
        color: #6c757d;
        margin-right: 8px;
        width: 18px;
        text-align: center;
    }
    
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #2c3e50;
        box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
    }
    
    .form-control:disabled {
        background-color: #f8f9fa;
        color: #6c757d;
        border-color: #e9ecef;
    }
    
    .alert {
        border-radius: 4px;
        border: none;
        padding: 14px 18px;
        margin-bottom: 25px;
        font-size: 14px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .btn-update {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ffffff;
        border: none;
        padding: 12px 40px;
        font-size: 15px;
        font-weight: 500;
        border-radius: 4px;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }
    
    .btn-update:hover {
        background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
        color: #ffffff;
    }
    
    .info-badge {
        display: inline-block;
        background: #e9ecef;
        color: #495057;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin-left: 8px;
    }
    
    @media (max-width: 768px) {
        .account-body {
            padding: 25px;
        }
        
        .account-header {
            padding: 20px;
        }
    }
</style>

<div class="account-container">
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="account-card" style="max-width:700px; width:100%;">
                <div class="account-header">
                    <h4><i class="fas fa-user-circle me-2"></i>Account Information</h4>
                </div>
                <div class="account-body">
                    <?php if($error != ""): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if($success != ""): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <!-- Account Details Section -->
                        <div class="section-title">
                            <i class="fas fa-id-card me-2"></i>Account Details
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-hashtag"></i>User ID
                                </label>
                                <input type="text" class="form-control" value="<?php echo $user_id; ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-barcode"></i>User Code
                                </label>
                                <input type="text" class="form-control" value="<?php echo $user_code; ?>" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i>Email Address
                            </label>
                            <input type="email" class="form-control" value="<?php echo $email; ?>" disabled>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-user-tag"></i>Account Type
                            </label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user_type); ?>" disabled>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="section-title">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user"></i>Full Name
                            </label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo $fullname; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-phone"></i>Contact Number
                            </label>
                            <input type="text" name="contact_number" class="form-control" value="<?php echo $contact; ?>" required>
                        </div>

                        <!-- Location Information Section -->
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt me-2"></i>Location Information
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-home"></i>Street Address
                            </label>
                            <input type="text" name="address" class="form-control" value="<?php echo $address; ?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">
                                    <i class="fas fa-city"></i>City
                                </label>
                                <input type="text" name="city" class="form-control" value="<?php echo $city; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-map"></i>Province/State
                                </label>
                                <input type="text" name="province" class="form-control" value="<?php echo $province; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-globe"></i>Country
                            </label>
                            <input type="text" name="country" class="form-control" value="<?php echo $country; ?>" required>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="update" class="btn btn-update">
                                <i class="fas fa-save me-2"></i>Update Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "tourist-footer.php"; ?>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>