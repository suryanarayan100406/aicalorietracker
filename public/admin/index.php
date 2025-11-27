<?php
// admin-dashboard.php
// Enable error reporting for development (turn off in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Auth (make sure this path is correct on your server) ---
require_once __DIR__ . '/../../app/lib/auth.php';
require_admin(); // assumes auth.php defines this function

// --- Database credentials (consider moving to environment variables in production) ---
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40061703_ai';
$user = 'if0_40061703';
$pass = 'RC7ihZAkxMTVle';

// Initialize output variables
$dbError = null;
$userCount = 0;
$uploadCount = 0;

try {
    // Create PDO with safe defaults
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Total users (cast to int)
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $userCount = (int) $stmt->fetchColumn();

    // Uploads in last 24 hours (cast to int)
    $stmt = $pdo->query("SELECT COUNT(*) FROM meals WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $uploadCount = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    // Don't expose raw DB error to users in production. Log it and show a friendly message.
    $dbError = "Could not connect to the database. Please contact the administrator.";
    error_log('DB Connection error in admin-dashboard.php: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Admin Dashboard — Calorie AI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet" />

    <style>
        /* (CSS from your theme; left as-is but trimmed for brevity) */
        :root {
            --bg-main: #050505;
            --text-primary: #fff;
            --text-secondary: #a1a1aa;
            --accent-glow: #00f5a0;
            --admin-accent-glow: #00d9f5;
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 90px;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            background-color:var(--bg-main);
            color:var(--text-primary);
            font-family:'Inter',sans-serif;
            min-height:100vh;
            line-height:1.6;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(0,245,160,0.12), transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0,217,245,0.08), transparent 40%);
            background-attachment: fixed;
        }
        .dashboard-wrapper{display:flex;width:100%;min-height:100vh}
        .sidebar{width:var(--sidebar-width);position:fixed;left:0;top:0;height:100vh;padding:2.5rem;background:rgba(255,255,255,0.03);backdrop-filter:blur(20px);border-right:1px solid rgba(255,255,255,0.08);z-index:10}
        
        /* === FIXED RULE === */
        /* The error was here. `padding: 3rem` was overwriting `padding-left`. */
        .dashboard-content {
            flex: 1;
            /* Set 3rem padding for top, right, and bottom */
            padding: 3rem; 
            /* Override *only* left padding to add sidebar width + 3rem gutter */
            padding-left: calc(var(--sidebar-width) + 3rem);
            min-height: 100vh;
        }

        .glass-card{background:rgba(255,255,255,0.03);border-radius:16px;padding:1.5rem;margin-bottom:1rem;border:1px solid rgba(255,255,255,0.06);backdrop-filter:blur(8px)}
        .header-bar h1{font-size:2.2rem;color:var(--admin-accent-glow);font-weight:800;margin-bottom:1rem}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
        .stat-card .label{color:var(--text-secondary);margin-bottom:0.5rem}
        .stat-card .value{font-size:2rem;font-weight:800;color:var(--admin-accent-glow)}
        .db-error{background:rgba(255,50,100,0.06);border-radius:8px;padding:1rem;color:#ffb3c6;border:1px solid rgba(255,50,100,0.18);margin-bottom:1rem}
        
        /* === FIXED RESPONSIVE RULE === */
        /* Added .dashboard-wrapper { flex-direction: column; } */
        /* Cleaned up .dashboard-content padding rule */
        @media (max-width: 900px) {
            .dashboard-wrapper {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 1rem;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            }
            .dashboard-content {
                /* This now correctly applies padding without a redundant padding-left */
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div>
                <a href="../index.html" class="logo" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit">
                    <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#00f5a0,#00d9f5);box-shadow:0 0 18px rgba(0,217,245,0.18)"></div>
                    <span style="font-weight:800;font-size:1.25rem">CalorieAI</span>
                </a>

                <nav class="sidebar-nav" style="margin-top:2rem;display:flex;flex-direction:column;gap:0.5rem">
                    <span style="font-size:.75rem;color:var(--text-secondary);text-transform:uppercase">Admin Menu</span>
                    <a href="index.php" style="display:flex;align-items:center;gap:.75rem;padding:.6rem;border-radius:8px;background:rgba(255,255,255,0.03);color:inherit;text-decoration:none;font-weight:600">Dashboard</a>
                    <a href="users.php" style="display:block;color:var(--text-secondary);text-decoration:none;padding:.6rem;border-radius:8px">User Management</a>
                    <a href="fooddb.php" style="display:block;color:var(--text-secondary);text-decoration:none;padding:.6rem;border-radius:8px">Food DB</a>
                </nav>
            </div>

            <div style="margin-top:2rem">
                <nav class="sidebar-nav user-menu" style="display:flex;flex-direction:column;gap:.5rem">
                    <a href="../login.php?logout=1" style="color:var(--text-secondary);text-decoration:none;padding:.6rem;border-radius:8px;display:inline-block">Logout</a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-content">
            <div class="header-bar">
                <h1>Admin Dashboard</h1>
            </div>

            <?php if (!empty($dbError)) : ?>
                <div class="db-error" role="alert">
                    <strong>Database Error:</strong> <?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid" aria-live="polite">
                <div class="stat-card glass-card">
                    <div class="label">Total Users</div>
                    <div class="value"><?php echo htmlspecialchars((int)$userCount, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="stat-card glass-card">
                    <div class="label">Uploads (24H)</div>
                    <div class="value"><?php echo htmlspecialchars((int)$uploadCount, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="stat-card glass-card">
                    <div class="label">Other Stats</div>
                    <div class="value">--</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const wrapper = document.querySelector('.dashboard-wrapper');
            const toggleKey = 'calorie-ai-sidebar-collapsed';
            // small responsive collapse state; you can wire up a button to toggle this if needed
            if (localStorage.getItem(toggleKey) === 'true') {
                wrapper.classList.add('sidebar-collapsed');
            }
        })();
    </script>
</body>
</html>