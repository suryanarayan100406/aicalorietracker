<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../app/lib/auth.php';
require_admin();

// Database connection details from the previous file
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40061703_ai';
$user = 'if0_40061703';
$pass = 'RC7ihZAkxMTVle';

$dbError = null;
$users = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle ban/unban actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
        $action = $_POST['action'] ?? '';
        $userId = $_POST['user_id'];
        
        if ($action === 'ban_user') {
            $stmt = $pdo->prepare('UPDATE users SET is_banned = 1 WHERE id = ?');
            $stmt->execute([$userId]);
        } elseif ($action === 'unban_user') {
            $stmt = $pdo->prepare('UPDATE users SET is_banned = 0 WHERE id = ?');
            $stmt->execute([$userId]);
        }
        // Redirect to avoid form resubmission
        header('Location: users.php');
        exit;
    }

    // Fetch all users
    $users = $pdo->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();

} catch (PDOException $e) {
    $dbError = "Database connection failed: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $dbError = "An error occurred: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — Calorie AI</title>
    <!-- Load premium-feel fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        /* --- CSS RESET & VARIABLES (Same as theme) --- */
        :root {
            --bg-main: #050505;
            --bg-secondary: #111111;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --accent-glow: #00f5a0; /* Vibrant teal/green glow */
            --accent-gradient: linear-gradient(135deg, #00f5a0, #00d9f5);
            --admin-accent-glow: #00d9f5; /* Use the blue for admin */
            --danger-glow: #ff4d80;
            --danger-bg: rgba(255, 77, 128, 0.1);
            --danger-border: rgba(255, 77, 128, 0.3);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 90px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
            
            /* Add background glow effect */
            background-image: radial-gradient(circle at 10% 20%, rgba(0, 245, 160, 0.15), transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(0, 217, 245, 0.1), transparent 40%);
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        /* --- Dashboard Layout --- */
        .dashboard-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* --- Sidebar (Glass) --- */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.03);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 10;
            transition: width 0.3s ease;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-primary);
        }
        
        .logo .logo-text {
            opacity: 1;
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }

        .logo-icon {
            width: 30px;
            height: 30px;
            background: var(--accent-gradient);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 245, 160, 0.4);
            flex-shrink: 0;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .sidebar-nav-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 1rem;
            opacity: 1;
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }

        .sidebar-nav a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }
        
        .sidebar-nav a .link-text {
            opacity: 1;
            transition: opacity 0.2s ease, width 0.2s ease, margin 0.2s ease;
            white-space: nowrap;
            margin-left: 0;
        }

        .sidebar-nav a svg {
            flex-shrink: 0;
        }
        
        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }
        
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1rem;
            white-space: nowrap;
        }
        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
        .sidebar-toggle svg {
            stroke: currentColor;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        /* --- Dashboard Content Area --- */
        .dashboard-content {
            flex-grow: 1;
            padding-left: var(--sidebar-width); /* Offset for sidebar */
            min-height: 100vh;
            padding: 3rem;
            transition: padding-left 0.3s ease;
        }
        
        .header-bar {
            margin-bottom: 2rem;
        }
        
        .header-bar h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--admin-accent-glow); /* Admin Blue */
        }
        
        /* --- Glass Card --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            margin-bottom: 1.5rem;
            overflow-x: auto; /* Allow table to scroll horizontally if needed */
        }
        
        .db-error {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-glow);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        /* --- Table Styling --- */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            min-width: 600px; /* Ensure table has a minimum width */
        }
        .admin-table thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .admin-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            color: var(--text-primary);
        }
        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }
        .admin-table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }
        
        /* Table button styles */
        .btn-table {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-ban {
            background: var(--danger-bg);
            color: var(--danger-glow);
            border: 1px solid var(--danger-border);
        }
        .btn-ban:hover {
            background: var(--danger-glow);
            color: var(--bg-main);
            box-shadow: 0 0 15px var(--danger-glow);
        }
        .btn-unban {
            background: rgba(255,255,255,0.1);
            color: var(--text-secondary);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-unban:hover {
            background: rgba(255,255,255,0.2);
            color: var(--text-primary);
        }
        .status-banned {
            color: var(--danger-glow);
            font-weight: 600;
        }
        .status-active {
            color: var(--accent-glow);
            font-weight: 600;
        }


        /* --- COLLAPSED STATE STYLES --- */
        .dashboard-wrapper.sidebar-collapsed .sidebar {
            width: var(--sidebar-width-collapsed);
        }
        .dashboard-wrapper.sidebar-collapsed .dashboard-content {
            padding-left: var(--sidebar-width-collapsed);
        }
        .dashboard-wrapper.sidebar-collapsed .sidebar .link-text,
        .dashboard-wrapper.sidebar-collapsed .sidebar .logo .logo-text,
        .dashboard-wrapper.sidebar-collapsed .sidebar .sidebar-nav-title {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            margin-left: 0;
        }
        .dashboard-wrapper.sidebar-collapsed .sidebar .logo {
            justify-content: center;
        }
        .dashboard-wrapper.sidebar-collapsed .sidebar-nav a {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .dashboard-wrapper.sidebar-collapsed .sidebar-toggle {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .dashboard-wrapper.sidebar-collapsed .sidebar-toggle svg {
            transform: rotate(180deg);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .dashboard-wrapper {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100% !important;
                height: auto;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                flex-direction: row;
                justify-content: space-between;
                padding: 1.5rem;
                gap: 1rem;
            }
            .sidebar-nav {
                display: none;
            }
            .sidebar-nav.user-menu {
                display: block;
            }
            .sidebar-toggle {
                display: none;
            }
            .dashboard-content {
                padding-left: 0 !important;
                padding: 2rem 1rem;
            }
            .header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <a href="../index.html" class="logo">
                    <div class="logo-icon"></div>
                    <span class="logo-text">CalorieAI</span>
                </a>
                
                <nav class="sidebar-nav">
                    <span class="sidebar-nav-title" style="margin-top: 3rem;">Admin Menu</span>
                    <a href="index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span class="link-text">Dashboard</span>
                    </a>
                    <a href="users.php" class="active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span class="link-text">User Management</span>
                    </a>
                    <a href="fooddb.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        <span class="link-text">Food DB</span>
                    </a>
                </nav>
            </div>
            
            <div>
                <nav class="sidebar-nav user-menu">
                    <a href="../login.php?logout=1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="link-text">Logout</span>
                    </a>
                </nav>
                
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span class="link-text">Collapse</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Dashboard Content -->
        <main class="dashboard-content">
        
            <div class="header-bar">
                <h1>User Management</h1>
            </div>

            <!-- DB Error Message -->
            <?php if (isset($dbError)): ?>
                <div class="db-error">
                    <strong>Database Error:</strong> <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>

            <!-- User Table -->
            <section class="glass-card">
                <table class="admin-table" aria-label="User list">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Username</th>
                            <th scope="col">Email</th>
                            <th scope="col">Banned?</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['is_banned']): ?>
                                        <span class="status-banned">Yes</span>
                                    <?php else: ?>
                                        <span class="status-active">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$u['is_banned']): ?>
                                        <form method="POST" style="margin: 0;">
                                            <button name="action" value="ban_user" class="btn-table btn-ban">Ban</button>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="margin: 0;">
                                            <button name="action" value="unban_user" class="btn-table btn-unban">Unban</button>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            
        </main>
        
    </div> <!-- end .dashboard-wrapper -->

    <script>
        (function(){
            // --- Sidebar Toggle Logic ---
            const wrapper = document.querySelector('.dashboard-wrapper');
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebarStateKey = 'calorie-ai-sidebar-collapsed';

            if (wrapper && toggleBtn) {
                // 1. Check localStorage for saved state
                let isCollapsed = localStorage.getItem(sidebarStateKey) === 'true';
                if (isCollapsed) {
                    wrapper.classList.add('sidebar-collapsed');
                }

                // 2. Add click event
                toggleBtn.addEventListener('click', function() {
                    isCollapsed = !isCollapsed; // Toggle the state
                    wrapper.classList.toggle('sidebar-collapsed', isCollapsed);
                    localStorage.setItem(sidebarStateKey, isCollapsed);
                });
            }
        })();
    </script>

</body>
</html>
