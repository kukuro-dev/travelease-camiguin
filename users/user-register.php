<?php
session_start();
include "../db.php"; // Database connection

$error = "";
$success = "";

if(isset($_POST['register'])){
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = $_POST['user_type'];
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $country = trim($_POST['country']);

    if($password !== $confirm_password){
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Get last user_id and increment
            $result = $conn->query("SELECT user_id FROM users ORDER BY date_created DESC LIMIT 1");
            if($row = $result->fetch_assoc()){
                $lastNumber = intval(substr($row['user_id'], 3)); // Remove "USR"
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 10000; // First user
            }

            $user_id = "USR" . $newNumber;
            $user_code = "USC" . $newNumber;

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users 
                (user_id, user_code, fullname, email, password, user_type, contact_number, address, city, province, country, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->bind_param(
                "sssssssssss",
                $user_id,
                $user_code,
                $fullname,
                $email,
                $hashed_password,
                $user_type,
                $contact_number,
                $address,
                $city,
                $province,
                $country
            );

            if($stmt->execute()){
                $success = "Registration successful!<br>
                            Your User ID: <strong>$user_id</strong><br>
                            Your User Code: <strong>$user_code</strong><br>
                            You can now <a href='user-login.php'>login</a>.";
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 
<style>
:root {
    --primary-dark: #1a1a1a;
    --secondary-dark: #2d2d2d;
    --accent-gray: #404040;
    --light-gray: #f5f5f5;
    --border-gray: #e0e0e0;
    --text-dark: #212529;
    --text-muted: #6c757d;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    min-height: 100vh;
}

.register-section {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 150px);
    padding: 60px 20px;
}

.register-container {
    width: 100%;
    max-width: 950px;
}

.register-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 1px solid var(--border-gray);
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.register-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
    padding: 35px 30px;
    text-align: center;
    color: white;
}

.logo-icon {
    width: 70px;
    height: 70px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.logo-icon i {
    font-size: 2rem;
    color: var(--primary-dark);
}

.register-header h2 {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.register-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.register-body {
    padding: 40px;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border-gray);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary-dark);
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
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
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

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: block;
    font-size: 0.85rem;
}

.input-wrapper {
    position: relative;
    width: 100%;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #6c757d;
    pointer-events: none;
    z-index: 2;
}

.input-wrapper .form-control,
.input-wrapper .form-select {
    width: 100%;
    height: 48px;
    padding-left: 45px;
    padding-right: 45px;
    box-sizing: border-box;
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
}

.input-wrapper .form-control:focus,
.input-wrapper .form-select:focus {
    outline: none;
    border-color: var(--primary-dark);
    box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1);
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #6c757d;
    cursor: pointer;
    z-index: 2;
    pointer-events: auto;
}

.password-toggle:hover {
    color: var(--primary-dark);
}

.register-btn {
    width: 100%;
    padding: 14px;
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
    margin-top: 30px;
}

.register-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 26, 26, 0.3);
}

.register-btn:active {
    transform: translateY(0);
}

.login-link {
    text-align: center;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid var(--border-gray);
}

.login-link p {
    color: var(--text-muted);
    margin: 0 0 8px 0;
    font-size: 0.9rem;
}

.login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.login-link a:hover {
    color: #764ba2;
    gap: 8px;
}

.account-type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.account-type-option {
    display: none;
}

.account-type-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border: 2px solid var(--border-gray);
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.account-type-label i {
    font-size: 2.5rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.account-type-label span {
    font-size: 1rem;
    color: var(--text-dark);
    font-weight: 600;
}

.account-type-label small {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 5px;
}

.account-type-option:checked + .account-type-label {
    border-color: var(--primary-dark);
    background: var(--light-gray);
}

.account-type-option:checked + .account-type-label i {
    color: var(--primary-dark);
}

.spinner {
    display: none;
    width: 18px;
    height: 18px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.register-btn.loading .spinner {
    display: inline-block;
}

.register-btn.loading .btn-text {
    display: none;
}

@media (max-width: 768px) {
    .register-body {
        padding: 30px 25px;
    }
    
    .register-header {
        padding: 30px 20px;
    }
    
    .register-header h2 {
        font-size: 1.4rem;
    }

    .register-section {
        padding: 40px 15px;
    }

    .account-type-selector {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<?php include "landing-header.php"; ?>

<section class="register-section">
    <div class="register-container">
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <div class="logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Your Account</h2>
                <p>Join Camiguin Rentals today</p>
            </div>

            <!-- Body -->
            <div class="register-body">
                <?php if($error != ""): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if($success != ""): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="" method="post" id="registerForm">
                    <!-- Personal Information -->
                    <div class="section-title">
                        <i class="fas fa-user"></i> Personal Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="fullname" class="form-control" placeholder="Enter your full name" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Type -->
                    <div class="section-title">
                        <i class="fas fa-users"></i> Account Type
                    </div>

                    <div class="account-type-selector mb-4">
                        <div>
                            <input type="radio" name="user_type" value="tourist" id="tourist" class="account-type-option" required>
                            <label for="tourist" class="account-type-label">
                                <i class="fas fa-suitcase-rolling"></i>
                                <span>Tourist</span>
                                <small>Book rentals & explore</small>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="user_type" value="provider" id="provider" class="account-type-option" required>
                            <label for="provider" class="account-type-label">
                                <i class="fas fa-hotel"></i>
                                <span>Service Provider</span>
                                <small>List your properties</small>
                            </label>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="section-title">
                        <i class="fas fa-address-book"></i> Contact Information
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="text" name="contact_number" class="form-control" placeholder="Enter contact number" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-home input-icon"></i>
                                    <input type="text" name="address" class="form-control" placeholder="Enter your address" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-city input-icon"></i>
                                    <input type="text" name="city" class="form-control" placeholder="Enter your city" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Province</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                    <input type="text" name="province" class="form-control" placeholder="Enter your province" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-flag input-icon"></i>
                                    <input type="text" name="country" class="form-control" placeholder="Enter your country" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="register" class="register-btn">
                        <span class="btn-text">
                            <i class="fas fa-user-plus"></i> Create Account
                        </span>
                        <div class="spinner"></div>
                    </button>
                </form>

                <div class="login-link">
                    <p>Already have an account?</p>
                    <a href="user-login.php">
                        Sign In <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "landing-footer.php"; ?>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
<script>
// Toggle password visibility
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
const confirmPassword = document.querySelector('#confirm_password');
toggleConfirmPassword.addEventListener('click', function () {
    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPassword.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

// Add loading state to register button
const registerForm = document.getElementById('registerForm');
const registerBtn = registerForm.querySelector('.register-btn');

registerForm.addEventListener('submit', function() {
    registerBtn.classList.add('loading');
});
</script>

</body>
</html>