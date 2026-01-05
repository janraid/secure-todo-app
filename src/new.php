<?php
session_start();

require __DIR__ . '/db.php';
if ($conn->connect_errno) {
    error_log('DB connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo 'Database connection error.';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
    if ($user_id <= 0) {
        $error = 'You must be logged in to create tasks.';
    } else {
        $name = trim($_POST['task_name'] ?? '');
        $details = trim($_POST['task_details'] ?? '');
        $due = trim($_POST['task_due'] ?? '');
        if ($name === '') {
            $error = 'Task name is required.';
        } else {
            if ($due === '') {
                $sql = "INSERT INTO tasks (userid, name, task) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('iss', $user_id, $name, $details);
                    $ok = $stmt->execute();
                    $stmt->close();
                } else {
                    $ok = false;
                }
            } else {
                // attempt to insert due date (assumes `due` is DATE or DATETIME)
                $sql = "INSERT INTO tasks (userid, name, task, due) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('isss', $user_id, $name, $details, $due);
                    $ok = $stmt->execute();
                    $stmt->close();
                } else {
                    $ok = false;
                }
            }
            if (!empty($ok)) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Failed to create task.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=TASA+Orbiter:wght@400..800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../styles/new.css" />
    <title>Dashboard</title>
</head>

<body>
    <div class="overlay"><span>The Secure Todo App</span></div>
    <aside class="d-flex flex-nowrap min-vh-100">
        <div class="thisone d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary vh-100" style="width: 280px;"> <a
                href="/"
                class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                <img src="../assets/icons/animal.png" class="bi pe-none me-2" width="40" aria-hidden="true">
                <span class="fs-4">Dashboard</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="./dashboard.php" class="nav-link link-body-emphasis ps-3" aria-current="page">
                        <img src="../assets/icons/home.png" class="bi pe-none me-2 mb-1" width="18" height="18"
                            aria-hidden="true">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-bg-success active">
                        <img style="filter:invert(1)" src="../assets/icons/plus.png" class="bi pe-none me-2 mb-1"
                            width="16" aria-hidden="true">
                        New Task
                    </a>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="nav-link link-body-emphasis">
                    <img src="../assets/icons/logout.png" class="bi pe-none me-2 mb-1" width="16" aria-hidden="true">
                    Logout
                </a>
                <!-- <strong><?php echo $_SESSION['username']; ?></strong> -->
            </div>
        </div>
    </aside>
    <main>
        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Create new task</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" class="needs-validation" novalidate="">
                <div class="mb-3">
                    <label for="taskName" class="form-label">Task Name</label>
                    <input name="task_name" type="text" class="form-control" id="taskName"
                        placeholder="Enter your task name here" required />
                </div>
                <div class="mb-3">
                    <label for="taskDetails" class="form-label">Task Details</label>
                    <textarea name="task_details" class="form-control" id="taskDetails" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="taskDue" class="form-label">Due Date (optional)</label>
                    <input name="task_due" type="date" class="form-control" id="taskDue" />
                </div>
                <button type="submit" class="btn btn-success px-5">Create</button>
            </form>
        </div>
    </main>