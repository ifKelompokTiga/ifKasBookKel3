// =====================================================
// BukuKas Universal — Auth Logic
// =====================================================

const Auth = (() => {
  function init() {
    if (Store.isLoggedIn()) {
      showApp();
    } else {
      showAuth();
      showLogin();
    }
  }

  function showAuth() {
    document.getElementById('app').style.display = 'none';
    const fab = document.getElementById('fabBtn');
    if (fab) fab.style.display = 'none';
    const bottomNav = document.querySelector('.bottom-nav');
    if (bottomNav) bottomNav.style.display = 'none';
    document.getElementById('authContainer').style.display = 'flex';
  }

  function showApp() {
    document.getElementById('authContainer').style.display = 'none';
    document.getElementById('app').style.display = 'flex';
    // Let app.js logic take over re-displaying fab/bottomNav based on media query if needed
    // Store.init() is called from app.js, so user data should be available
  }

  function showLogin() {
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
  }

  function showRegister() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
  }

  function doLogin() {
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();
    
    try {
      Store.login(email, password);
      // Reload page to ensure all data contexts are refreshed
      window.location.reload();
    } catch (e) {
      if (window.UI) UI.toast(e.message, 'danger');
      else alert(e.message);
    }
  }

  function doRegister() {
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value.trim();

    try {
      Store.register(name, email, password);
      if (window.UI) UI.toast('Akun berhasil dibuat. Silakan masuk.', 'success');
      else alert('Akun berhasil dibuat. Silakan masuk.');
      
      // Clear forms
      document.getElementById('formRegister').reset();
      showLogin();
      
      // Auto fill email
      document.getElementById('loginEmail').value = email;
    } catch (e) {
      if (window.UI) UI.toast(e.message, 'danger');
      else alert(e.message);
    }
  }

  function doLogout() {
    Store.logout();
    window.location.reload();
  }

  return {
    init,
    showAuth,
    showApp,
    showLogin,
    showRegister,
    doLogin,
    doRegister,
    doLogout
  };
})();

// Automatically initialize auth check before app loads fully
document.addEventListener('DOMContentLoaded', () => {
  Auth.init();
});
