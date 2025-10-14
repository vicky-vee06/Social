<?php
// Add your PHP code here
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign In</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
</head>
<body>
  <div class="container">
  <form class="login-form" action="./inc/login_process.php" method="POST">
      <h2>Sign In</h2>
      <div class="input-group">
        <i class="fas fa-envelope icon"></i>
        <input type="email" name="email" placeholder="Enter your email address" required />
      </div>
      <div class="input-group password-group">
        <i class="fas fa-lock icon"></i>
        <input type="password" name="password" placeholder="Enter your password" id="password" required />
        <i class="far fa-eye toggle-password" id="togglePassword"></i>
      </div>
      <div class="options">
        <label><input type="checkbox" checked /> Stay logged in</label>
        <a href="#">forgotten password?</a>
      </div>
      <button name="login" type="submit">Sign In</button>

      <div class="divider">Or Sign In with</div>
      <div class="social-icons">
        <a class="google" href="/auth/google" title="Sign in with Google" aria-label="Sign in with Google" target="_blank" rel="noopener noreferrer">
          <!-- Inline multicolor Google G for crisp rendering -->
          <svg viewBox="0 0 48 48" role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
            <path fill="#EA4335" d="M24 9.5c3.9 0 6.5 1.6 8 2.9l6-5.8C35.6 3.5 30.4 1.5 24 1.5 14 1.5 5.7 6.9 2.2 14.6l7.3 5.7C11.8 15.2 17.2 9.5 24 9.5z" />
            <path fill="#34A853" d="M46.5 24.5c0-1.6-.1-2.8-.4-4H24v8h13c-.6 3.4-3 6.1-6.3 7.6l7.2 5.6C44.5 36.7 46.5 31.9 46.5 24.5z" />
            <path fill="#4A90E2" d="M9.5 29.9c-1.1-3.3-1.1-6.9 0-10.2L2.2 14.6C-.6 20.7-.6 27.2 2.2 33.3l7.3-3.4z" />
            <path fill="#FBBC05" d="M24 46.5c6.4 0 11.6-2 15.3-5.4l-7.2-5.6c-2 1.4-4.5 2.3-8.1 2.3-6.8 0-12.2-5.7-12.7-12.9L2.2 33.3C5.7 40 14 46.5 24 46.5z" />
          </svg>
        </a>
        <a class="apple" href="/auth/apple" title="Sign in with Apple" aria-label="Sign in with Apple" target="_blank" rel="noopener noreferrer">
          <!-- Font Awesome Apple icon for easier styling -->
          <i class="fab fa-apple" aria-hidden="true" style="font-size:22px; line-height:1;"></i>
        </a>
      </div>
    </form>
  </div>

<script>
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');

  togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.classList.toggle('fa-eye-slash');
  });
</script>
</body>
</html>
