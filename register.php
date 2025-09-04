<?php
session_start();
//include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Foodyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box landscape">
            <div class="auth-left">
                <h2>Create Account</h2>
                <p class="auth-subtitle">Join Foodyo for the best food delivery experience</p>
                
                <div class="auth-image">
                    <img src="images/Registerr.jpg" alt="Register" class="register-image">
                </div>
                
                <?php
                // Display success message if exists
                if(isset($_SESSION['success'])) {
                    echo '<div class="success-message">'.$_SESSION['success'].'</div>';
                    unset($_SESSION['success']);
                }

                // Display errors if any
                if(isset($_SESSION['errors'])) {
                    echo '<div class="error-message">';
                    foreach($_SESSION['errors'] as $error) {
                        echo '<p>'.$error.'</p>';
                    }
                    echo '</div>';
                    unset($_SESSION['errors']);
                }
                ?>
            </div>

            <div class="auth-right">
                <form action="register_process.php" method="POST" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>"
                                   minlength="3" maxlength="50">
                        </div>

                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" required
                                   value="<?php echo isset($_SESSION['form_data']['fullname']) ? htmlspecialchars($_SESSION['form_data']['fullname']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}"
                                   value="<?php echo isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required rows="3"><?php echo isset($_SESSION['form_data']['address']) ? htmlspecialchars($_SESSION['form_data']['address']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="auth-button">Register</button>
                    </div>

                    <div class="auth-links">
                        <p>Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Phone number validation
        document.getElementById('phone').addEventListener('input', function() {
            const phone = this.value.replace(/\D/g, '');
            if (phone.length !== 10) {
                this.setCustomValidity('Phone number must be 10 digits');
            } else {
                this.setCustomValidity('');
            }
        });

        // Clear form data from session after page load
        <?php unset($_SESSION['form_data']); ?>
    </script>
</body>
</html>