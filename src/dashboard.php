<?php
session_start();

require __DIR__ . '/db.php';


$welcomeMessage = "";
$hour = (int) date('G');
if ($hour < 12) {
    $welcomeMessage = "Good Morning";
} elseif ($hour < 17) {
    $welcomeMessage = "Good Afternoon";
} else {
    $welcomeMessage = "Good Evening";
}


// Handle AJAX delete requests (pop task)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
    header('Content-Type: application/json');
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'invalid_id']);
        exit;

    }
    // find primary key column for tasks table
    $pk = 'id';
    $pkRes = $conn->query("SHOW KEYS FROM tasks WHERE Key_name = 'PRIMARY'");
    if ($pkRes && $pkRes->num_rows > 0) {
        $pkRow = $pkRes->fetch_assoc();
        if (!empty($pkRow['Column_name']))
            $pk = $pkRow['Column_name'];
    }
    $delSql = "DELETE FROM tasks WHERE `$pk` = ?";
    $delStmt = $conn->prepare($delSql);
    if (!$delStmt) {
        echo json_encode(['success' => false, 'error' => 'prepare_failed']);
        exit;
    }
    $delStmt->bind_param('i', $id);
    $ok = $delStmt->execute();
    $delStmt->close();
    echo json_encode(['success' => $ok ? true : false]);
    exit;
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
    <link rel="stylesheet" href="./styles/dash.css" />
    <title>Dashboard</title>
</head>

<body>
    <div class="overlay"><span>The Secure Todo App</span></div>
    <aside class="d-flex flex-nowrap min-vh-100">
        <div class="thisone d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary vh-100" style="width: 280px;"> <a
                href="/"
                class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                <img src="./assets/icons/animal.png" class="bi pe-none me-2" width="40" aria-hidden="true">
                <span class="fs-4">Dashboard</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="#" class="nav-link text-bg-success active ps-3" aria-current="page">
                        <img style="filter:invert(1)" src="./assets/icons/home.png" class="bi pe-none me-2 mb-1"
                            width="18" height="18" aria-hidden="true">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./new.php" class="nav-link link-body-emphasis ">
                        <img src="./assets/icons/plus.png" class="bi pe-none me-2 mb-1" width="16" aria-hidden="true">
                        New Task
                    </a>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="nav-link link-body-emphasis">
                    <img src="./assets/icons/logout.png" class="bi pe-none me-2 mb-1" width="16" aria-hidden="true">
                    Logout
                </a>
                <!-- <strong><?php echo $_SESSION['username']; ?></strong> -->
            </div>
        </div>
    </aside>
    <main>
        <header>
            <h1 class="text-capitalize"><?php echo $welcomeMessage ?>, <?php echo $_SESSION['username'] ?></h1>
            <div class="subhead fw-light">Browse your upcoming tasks</div>
        </header>
        <section>
            <?php
            // Fetch tasks for the logged-in user and render a card for each result (HTML template)
            $user_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
            if ($user_id > 0) {
                // detect primary key for tasks
                $pk = 'id';
                $pkRes = $conn->query("SHOW KEYS FROM tasks WHERE Key_name = 'PRIMARY'");
                if ($pkRes && $pkRes->num_rows > 0) {
                    $pkRow = $pkRes->fetch_assoc();
                    if (!empty($pkRow['Column_name']))
                        $pk = $pkRow['Column_name'];
                }

                // only select tasks that are not past due (due is NULL/empty or due date >= today)
                $sql = "SELECT * FROM tasks WHERE userid = ? AND (due IS NULL OR DATE(due) >= CURDATE())";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $taskId = isset($row[$pk]) ? (int) $row[$pk] : 0;
                            $title = $row['name'] ?? ($row['title'] ?? 'Untitled');
                            $due = $row['due'] ?? 'No due date';
                            $content = $row['task'] ?? $row['description'] ?? $row['content'] ?? '';
                            ?>
                            <div class="card task-card" data-task-id="<?= $taskId ?>" style="width: 18rem; cursor: pointer;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($title) ?></h5>
                                    <?php if ($due !== ''): ?>
                                        <h6 class="card-subtitle mb-2 text-body-secondary"><?= htmlspecialchars($due) ?></h6>
                                    <?php endif; ?>
                                    <p class="card-text"><?= htmlspecialchars($content) ?></p>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="text-muted">No tasks found.</div>
                        <?php
                    }
                    $stmt->close();
                } else {
                    ?>
                    <div class="text-danger">Failed to prepare task query.</div>
                    <?php
                }
            } else {
                ?>
                <div class="text-muted">Log in to see your tasks.</div>
                <?php
            }
            ?>
        </section>
    </main>
    <!--   
    <aside>

        <div class="groupone">
            <div class="iconcont">
                <img src="./assets/icons/animal.png" alt="cat icon" />
                <h1>Dashboard</h1>
            </div>

            <hr />
            <div class="list">
                <div class="clicked">
                    <img src="./assets/icons/home.png" alt="" />
                    <span>Home</span>
                </div>
                <div class="button">
                    <img src="./assets/icons/plus.png" alt="" />
                    <span>New Task</span>
                </div>
                <div class="button">
                    <img src="./assets/icons/logout.png" alt="" />
                    <span>Log Out</span>
                </div>
            </div>
        </div>
        <div class="user">
            <img src="" alt="" />
            <span class="usernameSpan"><?php echo $_SESSION['username']; ?></span>
        </div></aside>
     -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <!-- Pop confirmation modal -->
    <div class="modal fade" id="popConfirmModal" tabindex="-1" aria-labelledby="popConfirmLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="popConfirmLabel">Pop Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to <strong class="text-danger">pop</strong> this task?
                    This will permanently delete it.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="popConfirmBtn" class="btn btn-danger">Pop</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modalEl = document.getElementById('popConfirmModal');
            const bsModal = new bootstrap.Modal(modalEl);
            let currentTaskId = null;
            // Open modal when a card is clicked
            document.addEventListener('click', function (e) {
                const card = e.target.closest('.task-card');
                if (!card) return;
                currentTaskId = card.dataset.taskId;
                const title = card.querySelector('.card-title')?.textContent || '';
                modalEl.querySelector('.modal-body').textContent = `Pop task "${title}"? This will permanently delete it.`;
                bsModal.show();
            });

            document.getElementById('popConfirmBtn').addEventListener('click', function () {
                if (!currentTaskId) return;
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete_task&id=' + encodeURIComponent(currentTaskId)
                }).then(r => r.json()).then(json => {
                    if (json && json.success) {
                        const card = document.querySelector('.task-card[data-task-id="' + currentTaskId + '"]');
                        if (card) card.remove();
                        bsModal.hide();
                        // trigger layout recalculation
                        window.dispatchEvent(new Event('resize'));
                    } else {
                        alert('Failed to pop task');
                    }
                }).catch(() => alert('Failed to pop task'));
            });
        })();
    </script>
    <script>
        // JS span-resizer: measure card heights and set grid-row-end spans
        (function () {
            const gridSelector = 'section';

            function getNumericStyleProperty(el, prop) {
                return parseFloat(window.getComputedStyle(el).getPropertyValue(prop)) || 0;
            }

            function resizeAllGridItems() {
                const grid = document.querySelector(gridSelector);
                if (!grid) return;
                const rowHeight = getNumericStyleProperty(grid, 'grid-auto-rows');
                const rowGap = getNumericStyleProperty(grid, 'gap') || getNumericStyleProperty(grid, 'grid-row-gap');
                const items = grid.querySelectorAll('.card');
                items.forEach(item => {
                    // measure the content height inside the card
                    const content = item.querySelector('.card-body') || item;
                    const itemHeight = content.getBoundingClientRect().height;
                    const span = Math.max(1, Math.ceil((itemHeight + rowGap) / (rowHeight + rowGap)));
                    item.style.gridRowEnd = 'span ' + span;
                });
            }

            // Run on load and resize
            window.addEventListener('load', resizeAllGridItems);
            let resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resizeAllGridItems, 120);
            });

            // Observe DOM changes inside the grid (e.g., tasks added) and relayout
            const grid = document.querySelector(gridSelector);
            if (grid && window.MutationObserver) {
                const mo = new MutationObserver(() => resizeAllGridItems());
                mo.observe(grid, { childList: true, subtree: true });
            }
        })();
    </script>
</body>

</html>