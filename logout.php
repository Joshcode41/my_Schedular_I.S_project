<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logging Out...</title>
  <meta http-equiv="refresh" content="2;url=index.php">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
    }

    .overlay {
      background: rgba(0, 0, 0, 0.7);
      padding: 50px;
      border-radius: 12px;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <h2>🔒 Logged Out Successfully</h2>
    <p>You are being redirected to the homepage...</p>
  </div>
</body>
</html>
