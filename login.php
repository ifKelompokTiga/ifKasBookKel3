<?php
require 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: /nikko/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $user = loginUser($username, $password);

    if ($user) {
        header('Location: /nikko/index.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Nikko Kas</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    /* Base Styles & Background Gradient */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
        font-family: 'Inter', sans-serif; 
        /* Gradasi background biru gelap ke ungu */
        background: linear-gradient(135deg, #1e3a8a 0%, #3b0764 100%);
        min-height: 100vh; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        position: relative;
        overflow: hidden;
    }

    /* Dekorasi Latar Belakang (Lingkaran mengambang) */
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

    /* Wrapper utama agar z-index di atas dekorasi */
    .login-wrapper {
        z-index: 1;
        width: 100%;
        max-width: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 20px;
    }
    
    /* Card Container */
    .card { 
        width: 100%; 
        background: #ffffff; 
        border-radius: 16px; 
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); 
        overflow: hidden; /* Agar header mengikuti border-radius */
    }
    
    /* Header Card (Gradasi Biru/Ungu + Ikon Brankas) */
    .card-header { 
        background: linear-gradient(90deg, #0f766e 0%, #4338ca 100%);
        padding: 30px 20px;
        text-align: center;
        color: white;
    }
    .card-header i {
        font-size: 40px;
        color: #fbbf24; /* Warna emas/kuning */
        margin-bottom: 10px;
        text-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .card-header h2 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: 700; 
    }
    
    /* Body Card */
    .card-body {
        padding: 30px;
    }

    /* Form Styles */
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
        padding: 12px 16px 12px 40px; /* Padding kiri lebih besar untuk ikon */
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
        color: #6366f1; /* Ikon berubah warna saat fokus */
    }
    
    /* Button Styles (Gradasi) */
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
    
    /* Alert / Error Message */
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
    
    /* Hint / Demo Akun (Glassmorphism) */
    .hint-box { 
        width: 100%;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 16px;
        border-radius: 12px;
        text-align: center;
        font-size: 13px; 
        color: #e2e8f0; 
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
    }
    .hint-box i {
        color: #fbbf24;
        margin-right: 5px;
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
            <i class="fas fa-vault"></i> <h2>Nikko Kas</h2>
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
                  <label for="username">Username</label>
                  <div class="input-wrapper">
                      <input type="text" id="username" name="username" placeholder="Username" required />
                      <i class="fas fa-user"></i>
                  </div>
              </div>
              
              <div class="input-group">
                  <label for="password">Password</label>
                  <div class="input-wrapper">
                      <input type="password" id="password" name="password" placeholder="Password" required />
                      <i class="fas fa-lock"></i>
                  </div>
              </div>

              <button type="submit">Masuk</button>
            </form>
            <div class="text-link">
              Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </div>
      </div>

      <div class="hint-box">
          <i class="fas fa-lightbulb"></i> 
          <strong>Demo Akun:</strong> admin / admin123 &bull; user / user123
      </div>

  </div>
</body>
</html>