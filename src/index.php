<?php
session_start();
require __DIR__ . '/db.php';
if ($conn->connect_errno) {
  error_log('DB connection failed: ' . $conn->connect_error);
  http_response_code(500);
  echo 'Database connection error.';
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../styles/indexStyle.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=TASA+Orbiter:wght@400..800&display=swap" rel="stylesheet" />
  <title>Todo App</title>
</head>

<body>
  <div class="flexparent">
    <main>
      <div class="left">
        <div class="title">
          <h1>LOGIN</h1>
        </div>
        <form action="" method="POST">
          <p>USERNAME</p>
          <input type="text" name="username" id="" required />
          <p>PASSWORD</p>
          <input type="password" name="password" id="" required />
          <input type="submit" value="Login" />
          <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Sign up</a></p>
        </form>
      </div>
      <div class="right">
        <h2>The Secure Todo App</h2>
      </div>
    </main>
  </div>
</body>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Create account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" class="modal-body">
        <input type="hidden" name="action" value="register" />
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="reg_username" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="reg_password" type="password" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input name="reg_password_confirm" type="password" class="form-control" required />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? 'login';

  #comment
  if ($action === 'register') {
    $reg_user = trim($_POST['reg_username'] ?? '');
    $reg_pass = $_POST['reg_password'] ?? '';
    $reg_pass2 = $_POST['reg_password_confirm'] ?? '';
    if ($reg_user === '' || $reg_pass === '') {
      echo "<script>alert('Username and password are required.');</script>";
    } elseif ($reg_pass !== $reg_pass2) {
      echo "<script>alert('Passwords do not match.');</script>";
    } else {
      $q = "SELECT id FROM users WHERE username = ?";
      $s = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($s, 's', $reg_user);
      mysqli_stmt_execute($s);
      $res = mysqli_stmt_get_result($s);
      if ($res && mysqli_num_rows($res) > 0) {
        echo "<script>alert('Username already exists.');</script>";
      } else {
        $hash = hash('sha256', $reg_pass);
        $ins = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $si = mysqli_prepare($conn, $ins);
        $role = 'user';
        mysqli_stmt_bind_param($si, 'ss', $reg_user, $hash, $role);
        if (mysqli_stmt_execute($si)) {
          $_SESSION['id'] = mysqli_insert_id($conn);
          $_SESSION['username'] = $reg_user;
          header('Location: dashboard.php');
          exit;
        } else {
          echo "<script>alert('Failed to create account.');</script>";
        }
      }
    }
  } else {
    // login
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $query = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && $row = mysqli_fetch_assoc($result)) {
      $_SESSION['id'] = $row['id'];
      $stored_hash = $row['password'];
      $input_hash = hash('sha256', $password);
      if (is_string($stored_hash) && hash_equals($stored_hash, $input_hash)) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit;
      } else {
        echo "<script>alert('Invalid username or password.');</script>";
      }
    } else {
      echo "<script>alert('Invalid username or password.');</script>";
    }
  }
}
?>