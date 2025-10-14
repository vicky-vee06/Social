<?php
// Add your PHP code here
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Up</title>
  <link rel="stylesheet" href="./css/register.css" />
  <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
</head>
<body>
  <div class="container">
    <form class="signup-form login-form" action="./inc/save_register.php" method="POST">

      <h2>Sign Up</h2>

      <div class="input-group">
        <i class="fas fa-user icon"></i>
        <label for="name" class="sr-only">Name</label>
        <input type="text" id="name" name="name" placeholder="Name" required />
      </div>

      <div class="input-group">
        <i class="fas fa-envelope icon"></i>
        <label for="email" class="sr-only">Email address</label>
        <input type="email" id="email" name="email" placeholder="Email address" required />
      </div>

      <div class="input-group phone-group">
        <i class="fas fa-phone icon"></i>
        <label for="phone" class="sr-only">Phone number</label>
        
        <input type="tel" id="phone" name="phone" placeholder="Phone number" required />
        <input type="hidden" id="full-phone" name="full_phone" />
      </div>

      <div class="input-group password-group">
        <i class="fas fa-lock icon"></i>
        <label for="password" class="sr-only">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required />
        <i class="far fa-eye toggle-password" id="togglePassword"></i>
      </div>

      <div class="input-group password-group">
        <i class="fas fa-lock icon"></i>
        <label for="confirm-password" class="sr-only">Confirm password</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm password" required />
        <i class="far fa-eye toggle-password" id="toggleConfirmPassword"></i>
        <div id="pw-error" class="field-error" role="alert">Passwords do not match.</div>
      </div>

      <button type="submit" style="background: #8000FF; padding: 12px 0; border-radius: 8px; border: none; color: white; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(128, 0, 255, 0.2);">Sign Up</button>

      <div class="divider">Or Sign Up with</div>
      <div class="social-icons">
        <a class="google" href="https://accounts.google.com" title="Sign up with Google" aria-label="Sign up with Google" target="_blank" rel="noopener noreferrer">
          <!-- Inline Google G -->
          <svg viewBox="0 0 48 48" role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
            <path fill="#EA4335" d="M24 9.5c3.9 0 6.5 1.6 8 2.9l6-5.8C35.6 3.5 30.4 1.5 24 1.5 14 1.5 5.7 6.9 2.2 14.6l7.3 5.7C11.8 15.2 17.2 9.5 24 9.5z" />
            <path fill="#34A853" d="M46.5 24.5c0-1.6-.1-2.8-.4-4H24v8h13c-.6 3.4-3 6.1-6.3 7.6l7.2 5.6C44.5 36.7 46.5 31.9 46.5 24.5z" />
            <path fill="#4A90E2" d="M9.5 29.9c-1.1-3.3-1.1-6.9 0-10.2L2.2 14.6C-.6 20.7-.6 27.2 2.2 33.3l7.3-3.4z" />
            <path fill="#FBBC05" d="M24 46.5c6.4 0 11.6-2 15.3-5.4l-7.2-5.6c-2 1.4-4.5 2.3-8.1 2.3-6.8 0-12.2-5.7-12.7-12.9L2.2 33.3C5.7 40 14 46.5 24 46.5z" />
          </svg>
        </a>
        <a class="apple" href="/auth/apple" title="Sign up with Apple" aria-label="Sign up with Apple" target="_blank" rel="noopener noreferrer">
          <!-- Font Awesome Apple icon for easier styling -->
          <i class="fab fa-apple" aria-hidden="true" style="font-size:22px; line-height:1;"></i>
        </a>
      </div>

      <p style="margin-top:18px;color:#e8e8e8; font-size:13px">Already have an account? <a href="sign.php" style="color:white; font-weight:600; text-decoration:underline">Sign in</a></p>
    </form>
  </div>

  <script>
    // Toggle password visibility for both fields
    const pw = document.getElementById('password');
    const togglePw = document.getElementById('togglePassword');
    const cpw = document.getElementById('confirm-password');
    const toggleCpw = document.getElementById('toggleConfirmPassword');

    if (togglePw && pw) {
      togglePw.addEventListener('click', () => {
        const type = pw.getAttribute('type') === 'password' ? 'text' : 'password';
        pw.setAttribute('type', type);
        togglePw.classList.toggle('fa-eye-slash');
      });
    }

    if (toggleCpw && cpw) {
      toggleCpw.addEventListener('click', () => {
        const type = cpw.getAttribute('type') === 'password' ? 'text' : 'password';
        cpw.setAttribute('type', type);
        toggleCpw.classList.toggle('fa-eye-slash');
      });
    }

    // Basic client-side confirm password check (inline message)
    const form = document.querySelector('.signup-form');
    const pwError = document.getElementById('pw-error');
    if (form) {
      form.addEventListener('submit', (e) => {
        // password mismatch check
        if (pw && cpw && pw.value !== cpw.value) {
          e.preventDefault();
          if (pwError) pwError.classList.add('show');
          cpw.focus();
          return;
        }

        // prepare full phone value (prefix country code if not present)
        const countryCodeEl = document.getElementById('country-code');
        const fullPhoneEl = document.getElementById('full-phone');
        if (countryCodeEl && fullPhoneEl && phone) {
          const code = countryCodeEl.value || '';
          const raw = phone.value.trim();
          if (raw.length === 0) {
            fullPhoneEl.value = '';
          } else if (raw.startsWith(code)) {
            fullPhoneEl.value = raw;
          } else if (raw.startsWith('+' + code.replace('+',''))) {
            fullPhoneEl.value = raw;
          } else {
            // remove leading zeros from the local number to avoid +1 0123 -> +10123
            const local = raw.replace(/^0+/, '');
            fullPhoneEl.value = code + local;
          }
        }

        if (pwError) pwError.classList.remove('show');
      });
    }

    // Hide inline error when user edits either password field
    [pw, cpw].forEach((el) => {
      if (el) el.addEventListener('input', () => {
        if (pwError) pwError.classList.remove('show');
      });
    });
  </script>
</body>
</html>
