<?php
session_start();
include "../db.php";

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Check in users table first
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            // Store session data
            $_SESSION['user_id']        = $user['user_id'];
            $_SESSION['user_code']      = $user['user_code']; 
            $_SESSION['fullname']       = $user['fullname'];
            $_SESSION['email']          = $user['email'];
            $_SESSION['user_type']      = $user['user_type'];
            $_SESSION['contact_number'] = $user['contact_number'];
            $_SESSION['address']        = $user['address'];
            $_SESSION['city']           = $user['city'];
            $_SESSION['province']       = $user['province'];
            $_SESSION['country']        = $user['country'];
            $_SESSION['date_created']   = $user['date_created'];

            // Redirect based on user type
            if ($user['user_type'] === 'tourist') {
                header("Location: ../tourist-page/tourist-dashboard.php");
            } elseif ($user['user_type'] === 'provider') {
                header("Location: ../user-page/user-dashboard.php");
            } else {
                header("Location: ../login.php"); // fallback
            }
            exit;
        } else {
            $error = "Invalid email or password!";
        }

    } else {
        // 2. Check in admin table if not found in users
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // If your admin password is plain text (like "admin"), use this:
            if ($admin['password'] === $password) {
                session_regenerate_id(true);

                // Store admin session data
                $_SESSION['admin_id']       = $admin['admin_id'];
                $_SESSION['fullname']       = $admin['fullname'];
                $_SESSION['email']          = $admin['email'];
                $_SESSION['contact_number'] = $admin['contact_number'];
                $_SESSION['username']       = $admin['username'];

                // Redirect to admin dashboard
                header("Location: ../admin-page/admin-dashboard.php");
                exit;

            } else {
                $error = "Invalid email or password!";
            }

        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Camiguin Rentals</title>
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

.login-section {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 150px);
    padding: 60px 20px;
}

.login-container {
    width: 100%;
    max-width: 480px;
}

.login-card {
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

.login-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
    padding: 40px 30px;
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

.login-header h2 {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.login-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.login-body {
    padding: 40px;
}

.welcome-text {
    text-align: center;
    margin-bottom: 30px;
}

.welcome-text h3 {
    color: var(--text-dark);
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.welcome-text p {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
}

.alert {
    border-radius: 10px;
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

.alert::before {
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 1.2rem;
    content: "\f06a";
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: block;
    font-size: 0.9rem;
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

.input-wrapper .form-control {
    width: 100%;
    height: 52px;
    padding-left: 45px;   /* space for LEFT icon */
    padding-right: 45px;  /* space for eye icon */
    box-sizing: border-box;
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.input-wrapper .form-control:focus {
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

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    font-size: 0.85rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    cursor: pointer;
    margin: 0;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary-dark);
}

.forgot-password {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #764ba2;
}

.login-btn {
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
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 26, 26, 0.3);
}

.login-btn:active {
    transform: translateY(0);
}

.divider {
    text-align: center;
    margin: 25px 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--border-gray);
}

.divider span {
    background: white;
    padding: 0 15px;
    color: var(--text-muted);
    font-size: 0.8rem;
    position: relative;
    z-index: 1;
}

.social-login {
    display: flex;
    gap: 12px;
    margin-bottom: 25px;
}

.social-btn {
    flex: 1;
    padding: 12px;
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 500;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.social-btn:hover {
    border-color: var(--primary-dark);
    background: var(--light-gray);
    transform: translateY(-2px);
}

.social-btn i {
    font-size: 1.1rem;
}

.social-btn.google i {
    color: #DB4437;
}

.social-btn.facebook i {
    color: #4267B2;
}

.register-link {
    text-align: center;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid var(--border-gray);
}

.register-link p {
    color: var(--text-muted);
    margin: 0 0 8px 0;
    font-size: 0.9rem;
}

.register-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.register-link a:hover {
    color: #764ba2;
    gap: 8px;
}

.features-list {
    margin-top: 20px;
    padding: 20px;
    background: var(--light-gray);
    border-radius: 12px;
    border: 1px solid var(--border-gray);
}

.features-list h4 {
    font-size: 0.9rem;
    color: var(--text-dark);
    margin-bottom: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.features-list h4 i {
    color: #ffc107;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 6px 0;
    color: var(--text-dark);
    font-size: 0.85rem;
}

.feature-item i {
    color: #28a745;
    font-size: 0.9rem;
    margin-top: 2px;
}

@media (max-width: 576px) {
    .login-body {
        padding: 30px 25px;
    }
    
    .login-header {
        padding: 30px 20px;
    }
    
    .login-header h2 {
        font-size: 1.4rem;
    }
    
    .social-login {
        flex-direction: column;
    }

    .login-section {
        padding: 40px 15px;
    }
}

/* Loading spinner */
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

.login-btn.loading .spinner {
    display: inline-block;
}

.login-btn.loading .btn-text {
    display: none;
}
</style>
</head>
<body>

<?php include "landing-header.php"; ?>

<section class="login-section">
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <h2>Camiguin Rentals</h2>
                <p>Explore. Stay. Experience.</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <div class="welcome-text">
                    <h3>Welcome Back!</h3>
                    <p>Sign in to continue your journey</p>
                </div>

                <?php if($error != ""): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="post" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" name="login" class="login-btn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </span>
                        <div class="spinner"></div>
                    </button>
                </form>

                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>

                <div class="social-login">
                    <button class="social-btn google" type="button">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </button>
                    <button class="social-btn facebook" type="button">
                        <i class="fab fa-facebook-f"></i>
                        <span>Facebook</span>
                    </button>
                </div>

                <div class="features-list">
                    <h4><i class="fas fa-star"></i> What you can do:</h4>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Book amazing rentals and accommodations</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Discover tourist spots and attractions</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Manage your bookings and reviews</span>
                    </div>
                </div>

                <div class="register-link">
                    <p>Don't have an account?</p>
                    <a href="user-register.php">
                        Create Account <i class="fas fa-arrow-right"></i>
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

// Add loading state to login button
const loginForm = document.getElementById('loginForm');
const loginBtn = loginForm.querySelector('.login-btn');

loginForm.addEventListener('submit', function() {
    loginBtn.classList.add('loading');
});

// Social login buttons (placeholder functionality)
document.querySelectorAll('.social-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const provider = this.classList.contains('google') ? 'Google' : 'Facebook';
        alert(`${provider} login coming soon!`);
    });
});
</script>

</body>
</html>