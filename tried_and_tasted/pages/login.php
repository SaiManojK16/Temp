<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Tried & Tasted</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }
    .left-panel {
  flex: 1;
  background-color: #6c63ff; /* fallback if image fails */
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 0;
}

.left-panel img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}


    .right-panel {
      flex: 1;
      background-color: #ffffff;
      padding: 4rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .right-panel h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .right-panel p {
      margin-bottom: 2rem;
      color: #555;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 0.75rem;
      background-color: #00b894;
      color: white;
      border: none;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #019875;
    }

    .link {
      margin-top: 1rem;
      text-align: center;
    }

    .link a {
      text-decoration: none;
      color: #3b3b98;
      font-weight: bold;
    }

    .message {
      text-align: center;
      margin-top: 1rem;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="left-panel">
<img src="../assets/login_illustration.png" alt="Login Illustration">

</div>

<div class="right-panel">
  <h2>Welcome Back</h2>
  <p>Sign in to your Tried & Tasted account</p>

  <form id="loginForm">
    <div class="form-group">
      <input type="email" id="email" placeholder="Email" required />
    </div>
    <div class="form-group">
      <input type="password" id="password" placeholder="Password" required />
    </div>
    <button type="submit">Login</button>
    <div class="message" id="message"></div>
  </form>

  <div class="link">
    Don’t have an account? <a href="register.php">Create one</a>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const messageBox = document.getElementById('message');

  try {
    // 1️⃣ Authenticate user
    const res1 = await fetch('../api/user/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',              // ← send/receive cookies
      body: JSON.stringify({ email, password })
    });
    const text1 = await res1.text();
    const data1 = JSON.parse(text1);

    if (!data1.success) {
      messageBox.innerText = data1.message;
      messageBox.style.color = 'red';
      return;
    }

    // 2️⃣ Store frontend user
    localStorage.setItem('user', JSON.stringify(data1.user));

    // 3️⃣ Sync PHP session
    await fetch('../api/user/set_session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',              // ← critical!
      body: JSON.stringify({ user_id: data1.user.user_id })
    });

    messageBox.innerText = 'Logged in successfully';
    messageBox.style.color = 'green';
    setTimeout(() => {
      window.location.href = 'home.php';
    }, 1000);

  } catch (err) {
    messageBox.innerText = "Login failed: " + err.message;
    messageBox.style.color = 'red';
  }
});
</script>

</body>
</html>
