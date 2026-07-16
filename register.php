<?php
require 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: /nikko/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (!$name || !$username || !$password || !$confirm) {
        $error = 'Semua bidang harus diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $registerError = registerUser($username, $password, $name);
        if ($registerError) {
            $error = $registerError;
        } else {
            loginUser($username, $password);
            header('Location: /nikko/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Nikko Kas</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
        font-family: 'Inter', sans-serif; 
        background: linear-gradient(135deg, #1e3a8a 0%, #3b0764 100%);
        min-height: 100vh; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        position: relative;
        overflow: hidden;
    }

    .bg-shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.03);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        z-index: 0;
    }
    .shape-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
    .shape-2 { width: 200px; height: 200px; bottom: 50px; right: -50px; }
    .shape-3 { width: 150px; height: 150px; top: 50%; left: 80%; }

    .login-wrapper {
        z-index: 1;
        width: 100%;
        max-width: 420px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 20px;
    }
    
    .card { 
        width: 100%; 
        background: #ffffff; 
        border-radius: 16px; 
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); 
        overflow: hidden;
    }
    
    .card-header { 
        background: linear-gradient(90deg, #0f766e 0%, #4338ca 100%);
        padding: 30px 20px;
        text-align: center;
        color: white;
    }
    .card-header i {
        font-size: 40px;
        color: #fbbf24;
        margin-bottom: 10px;
        text-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .card-header h2 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: 700; 
    }
    
    .card-body {
        padding: 30px;
    }

    form { display: flex; flex-direction: column; gap: 18px; }
    
    .input-group { 
        position: relative;
        display: flex; 
        flex-direction: column; 
        gap: 6px; 
    }
    .input-group label { 
        font-size: 13px; 
        font-weight: 600; 
        color: #334155; 
    }
    
    .input-wrapper {
        position: relative;
    }
    .input-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }

    input { 
        width: 100%;
        padding: 12px 16px 12px 40px;
        border-radius: 8px; 
        border: 1.5px solid #cbd5e1; 
        font-size: 14px; 
        font-family: inherit;
        transition: all 0.3s ease;
        background-color: #f8fafc;
        color: #1e293b;
    }
    input:focus { 
        outline: none; 
        border-color: #6366f1; 
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); 
    }
    input:focus + i, .input-wrapper:focus-within i {
        color: #6366f1; 
    }
    
    button { 
        background: linear-gradient(90deg, #2563eb 0%, #3730a3 100%);
        color: white; 
        border: none; 
        padding: 14px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer; 
        font-family: inherit;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        transition: all 0.2s ease;
    }
    button:hover { 
        background: linear-gradient(90deg, #1d4ed8 0%, #312e81 100%);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }
    button:active { transform: translateY(2px); }
    
    .alert { 
        background: #fee2e2; 
        color: #b91c1c; 
        padding: 12px 16px; 
        border-radius: 8px; 
        font-size: 13px; 
        margin-bottom: 20px;
        border: 1px solid #fca5a5;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    
    .text-link {
        text-align: center;
        font-size: 13px;
        color: #475569;
    }
    .text-link a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
    }
    .text-link a:hover {
        text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>
  <div class="bg-shape shape-3"></div>

  <div class="login-wrapper">
      
      <div class="card">
        <div class="card-header">
            <i class="fas fa-vault"></i> <h2>Daftar Nikko Kas</h2>
        </div>

        <div class="card-body">
            <?php if ($error): ?>
              <div class="alert">
                  <i class="fas fa-exclamation-triangle"></i>
                  <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <form method="post">
              <div class="input-group">
                  <label for="name">Nama Lengkap</label>
                  <div class="input-wrapper">
                      <input type="text" id="name" name="name" placeholder="Nama lengkap" required />
                      <i class="fas fa-user"></i>
                  </div>
              </div>

              <div class="input-group">
                  <label for="username">Username</label>
                  <div class="input-wrapper">
                      <input type="text" id="username" name="username" placeholder="Username" required />
                      <i class="fas fa-user-check"></i>
                  </div>
              </div>
               
              <div class="input-group">
                  <label for="password">Password</label>
                  <div class="input-wrapper">
                      <input type="password" id="password" name="password" placeholder="Password" required />
                      <i class="fas fa-lock"></i>
                  </div>
              </div>

              <div class="input-group">
                  <label for="confirm_password">Konfirmasi Password</label>
                  <div class="input-wrapper">
                      <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required />
                      <i class="fas fa-lock"></i>
                  </div>
              </div>

              <button type="submit">Daftar</button>
            </form>

            <div class="text-link">
              Sudah punya akun? <a href="login.php">Masuk</a>
            </div>
        </div>
      </div>

      <div class="hint-box">
          <i class="fas fa-lightbulb"></i> 
          <strong>Catatan:</strong> Setelah daftar, Anda dapat langsung masuk ke dashboard.
      </div>

  </div>
</body>
</html>