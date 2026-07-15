<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Tampilkan pesan error jika ada
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Simpan data yang sudah diisi untuk keperluan validasi
$old_fullname = $_SESSION['old_fullname'] ?? '';
$old_username = $_SESSION['old_username'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_fullname']);
unset($_SESSION['old_username']);
unset($_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Buku Kas Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box register-box">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h2>Buku Kas Digital</h2>
                <p>Daftar akun baru untuk mulai mengelola keuangan</p>
            </div>
            
            <!-- Tampilkan pesan -->
            <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error alert-dismissible">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
            <?php endif; ?>
            
            <form action="auth.php" method="POST" class="auth-form" id="registerForm" novalidate>
                <input type="hidden" name="action" value="register">
                
                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="fullname">
                        <i class="fas fa-user"></i> Nama Lengkap <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="fullname" 
                        name="fullname" 
                        placeholder="Masukkan nama lengkap" 
                        value="<?php echo htmlspecialchars($old_fullname); ?>"
                        required
                        minlength="3"
                        maxlength="100"
                    >
                    <div class="form-error" id="fullnameError"></div>
                </div>
                
                <!-- Username -->
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user-tag"></i> Username <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username (min 3 karakter)" 
                        value="<?php echo htmlspecialchars($old_username); ?>"
                        required
                        minlength="3"
                        maxlength="50"
                        pattern="[a-zA-Z0-9_]+"
                    >
                    <div class="form-error" id="usernameError"></div>
                    <small class="form-hint">Hanya huruf, angka, dan underscore (_)</small>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Masukkan email aktif" 
                        value="<?php echo htmlspecialchars($old_email); ?>"
                        required
                        maxlength="100"
                    >
                    <div class="form-error" id="emailError"></div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password <span class="required">*</span>
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password (min 6 karakter)" 
                            required
                            minlength="6"
                            maxlength="100"
                        >
                        <span class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar" id="strengthBar"></div>
                        <span class="strength-text" id="strengthText">Kekuatan password: Lemah</span>
                    </div>
                    <div class="form-error" id="passwordError"></div>
                </div>
                
                <!-- Konfirmasi Password -->
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Konfirmasi Password <span class="required">*</span>
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Konfirmasi password" 
                            required
                            minlength="6"
                        >
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="form-error" id="confirmError"></div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="terms" name="terms" required>
                        <span class="checkmark"></span>
                        Saya setuju dengan <a href="#" onclick="showTerms()">Syarat & Ketentuan</a> dan 
                        <a href="#" onclick="showPrivacy()">Kebijakan Privasi</a>
                    </label>
                    <div class="form-error" id="termsError"></div>
                </div>
                
                <!-- Captcha sederhana -->
                <div class="form-group captcha-group">
                    <label>Verifikasi <span class="required">*</span></label>
                    <div class="captcha-box">
                        <span class="captcha-code" id="captchaCode"></span>
                        <button type="button" class="captcha-refresh" onclick="generateCaptcha()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <input 
                        type="text" 
                        id="captchaInput" 
                        name="captcha" 
                        placeholder="Masukkan kode verifikasi" 
                        required
                        maxlength="6"
                    >
                    <div class="form-error" id="captchaError"></div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login sekarang</a></p>
            </div>
            
            <div class="auth-social">
                <p>Atau daftar dengan:</p>
                <div class="social-buttons">
                    <a href="#" class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </a>
                    <a href="#" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Terms & Conditions -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Syarat & Ketentuan</h3>
                <span class="modal-close" onclick="closeTerms()">&times;</span>
            </div>
            <div class="modal-body">
                <h4>1. Penggunaan Aplikasi</h4>
                <p>Dengan mendaftar, Anda setuju untuk menggunakan aplikasi Buku Kas Digital dengan bijak.</p>
                
                <h4>2. Keamanan Data</h4>
                <p>Kami menjaga keamanan data Anda dengan enkripsi dan sistem keamanan terbaik.</p>
                
                <h4>3. Privasi</h4>
                <p>Data Anda akan dijaga kerahasiaannya dan tidak akan dibagikan ke pihak ketiga.</p>
            </div>
        </div>
    </div>
    
    <div id="privacyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Kebijakan Privasi</h3>
                <span class="modal-close" onclick="closePrivacy()">&times;</span>
            </div>
            <div class="modal-body">
                <h4>Data yang Dikumpulkan</h4>
                <p>Nama, username, email, dan password yang di-hash.</p>
                
                <h4>Penggunaan Data</h4>
                <p>Data digunakan untuk autentikasi dan manajemen akun.</p>
                
                <h4>Perlindungan Data</h4>
                <p>Data dienkripsi dan disimpan dengan aman di database.</p>
            </div>
        </div>
    </div>
    
    <script>
    // ===== FUNGSI VALIDASI =====
    
    // Toggle password visibility
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.parentElement.querySelector('.toggle-password i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    
    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let message = '';
        let color = '';
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                message = 'Lemah';
                color = '#dc3545';
                break;
            case 2:
                message = 'Cukup';
                color = '#ffc107';
                break;
            case 3:
                message = 'Baik';
                color = '#17a2b8';
                break;
            case 4:
            case 5:
                message = 'Kuat';
                color = '#28a745';
                break;
        }
        
        strengthBar.style.width = (strength * 20) + '%';
        strengthBar.style.background = color;
        strengthText.textContent = 'Kekuatan password: ' + message;
        strengthText.style.color = color;
    });
    
    // Validasi password match
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirm = this.value;
        const errorDiv = document.getElementById('confirmError');
        
        if (confirm && password !== confirm) {
            errorDiv.textContent = 'Password tidak cocok!';
            this.style.borderColor = '#dc3545';
        } else {
            errorDiv.textContent = '';
            this.style.borderColor = '#28a745';
        }
    });
    
    // Validasi username (hanya alfanumerik dan underscore)
    document.getElementById('username').addEventListener('input', function() {
        const value = this.value;
        const errorDiv = document.getElementById('usernameError');
        const pattern = /^[a-zA-Z0-9_]+$/;
        
        if (value && !pattern.test(value)) {
            errorDiv.textContent = 'Username hanya boleh huruf, angka, dan underscore (_)';
            this.style.borderColor = '#dc3545';
        } else {
            errorDiv.textContent = '';
            this.style.borderColor = '#28a745';
        }
    });
    
    // Validasi email real-time
    document.getElementById('email').addEventListener('input', function() {
        const value = this.value;
        const errorDiv = document.getElementById('emailError');
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value && !pattern.test(value)) {
            errorDiv.textContent = 'Format email tidak valid!';
            this.style.borderColor = '#dc3545';
        } else {
            errorDiv.textContent = '';
            this.style.borderColor = '#28a745';
        }
    });
    
    // ===== CAPTCHA =====
    function generateCaptcha() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let captcha = '';
        for (let i = 0; i < 6; i++) {
            captcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('captchaCode').textContent = captcha;
        return captcha;
    }
    
    // Generate captcha saat load
    let currentCaptcha = generateCaptcha();
    
    // ===== FORM SUBMISSION =====
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validasi Terms
        const terms = document.getElementById('terms');
        const termsError = document.getElementById('termsError');
        if (!terms.checked) {
            termsError.textContent = 'Anda harus menyetujui Syarat & Ketentuan';
            isValid = false;
        } else {
            termsError.textContent = '';
        }
        
        // Validasi Captcha
        const captchaInput = document.getElementById('captchaInput');
        const captchaError = document.getElementById('captchaError');
        if (captchaInput.value.toUpperCase() !== currentCaptcha) {
            captchaError.textContent = 'Kode verifikasi salah!';
            isValid = false;
            generateCaptcha();
            captchaInput.value = '';
        } else {
            captchaError.textContent = '';
        }
        
        // Validasi password match
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const confirmError = document.getElementById('confirmError');
        if (password !== confirm) {
            confirmError.textContent = 'Password tidak cocok!';
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll ke error pertama
            const firstError = document.querySelector('.form-error:not(:empty)');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // ===== MODAL FUNCTIONS =====
    function showTerms() {
        document.getElementById('termsModal').style.display = 'block';
        event.preventDefault();
    }
    
    function closeTerms() {
        document.getElementById('termsModal').style.display = 'none';
    }
    
    function showPrivacy() {
        document.getElementById('privacyModal').style.display = 'block';
        event.preventDefault();
    }
    
    function closePrivacy() {
        document.getElementById('privacyModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
    
    // ===== REAL-TIME VALIDATION =====
    // Fullname validation
    document.getElementById('fullname').addEventListener('input', function() {
        const value = this.value;
        const errorDiv = document.getElementById('fullnameError');
        if (value && value.length < 3) {
            errorDiv.textContent = 'Nama minimal 3 karakter';
            this.style.borderColor = '#dc3545';
        } else {
            errorDiv.textContent = '';
            this.style.borderColor = '#28a745';
        }
    });
    
    // ===== AUTO DISMISS ALERT =====
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
    </script>
</body>
</html>
