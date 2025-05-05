<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | Tried & Tasted</title>
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

    input[type="text"],
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
      background-color: #007bff;
      color: white;
      border: none;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0069d9;
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
<img src="../assets/login_illustration.png" alt="Register Illustration">

</div>

<div class="right-panel">
  <h2>Create Account</h2>
  <p>Sign up for your Tried & Tasted experience</p>

  <form id="registerForm">
    <div class="form-group">
      <input type="text" id="full_name" placeholder="Full Name" required />
    </div>
    <div class="form-group">
      <input type="email" id="email" placeholder="Email" required />
    </div>
    <div class="form-group">
      <input type="password" id="password" placeholder="Password" required />
    </div>
    <button type="submit">Register</button>
    <div class="message" id="message"></div>
  </form>

  <div class="link">
    Already have an account? <a href="login.php">Login</a>
  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const full_name = document.getElementById('full_name').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const messageBox = document.getElementById('message');

  try {
    const response = await fetch('../api/user/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ full_name, email, password })
    });

    const text = await response.text();
    let data;

    try {
      data = JSON.parse(text);
    } catch {
      throw new Error("Invalid JSON response from server");
    }

    messageBox.innerText = data.message || "Something went wrong";
    messageBox.style.color = data.success ? 'green' : 'red';

    if (data.success) {
      setTimeout(() => {
        window.location.href = 'login.php';
      }, 1500);
    }
  } catch (err) {
    messageBox.innerText = "Registration failed: " + err.message;
    messageBox.style.color = 'red';
  }
});
</script>

</body>
</html>
