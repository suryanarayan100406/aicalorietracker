<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../app/lib/auth.php';
require_admin(); // Added for security, matching users.php

// Database connection details from users.php
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40061703_ai';
$user = 'if0_40061703';
$pass = 'RC7ihZAkxMTVle';

$dbError = null;
$foods = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $food_id = $_POST['food_id'] ?? null;
        
        // Use the correct table name, assuming 'food_db' based on the old controller
        if ($action === 'add_food' && !empty($_POST['name']) && isset($_POST['grams']) && isset($_POST['cal'])) {
            $stmt = $pdo->prepare('INSERT INTO food_db (name, typical_portion_grams, calories_per_100g) VALUES (?, ?, ?)');
            $stmt->execute([$_POST['name'], $_POST['grams'], $_POST['cal']]);
        } elseif ($action === 'edit_food' && $food_id && !empty($_POST['name']) && isset($_POST['grams']) && isset($_POST['cal'])) {
            $stmt = $pdo->prepare('UPDATE food_db SET name = ?, typical_portion_grams = ?, calories_per_100g = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['grams'], $_POST['cal'], $food_id]);
        } elseif ($action === 'remove_food' && $food_id) {
            $stmt = $pdo->prepare('DELETE FROM food_db WHERE id = ?');
            $stmt->execute([$food_id]);
        }
        
        // Redirect to avoid form resubmission
        header('Location: fooddb.php');
        exit;
    }

    // Fetch all foods
    $foods = $pdo->query('SELECT * FROM food_db ORDER BY name ASC')->fetchAll();

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
    <title>Food DB Management — Calorie AI</title>
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
        
        .glass-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
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
            min-width: 700px; /* Ensure table has a minimum width */
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
            margin: 0 0.25rem;
        }
        .btn-danger {
            background: var(--danger-bg);
            color: var(--danger-glow);
            border: 1px solid var(--danger-border);
        }
        .btn-danger:hover {
            background: var(--danger-glow);
            color: var(--bg-main);
            box-shadow: 0 0 15px var(--danger-glow);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: var(--text-secondary);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            color: var(--text-primary);
        }
        
        /* Table Input Styles */
        .table-input {
            padding: 0.5rem 0.75rem;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }
        .table-input:focus {
            outline: none;
            border-color: var(--admin-accent-glow);
            box-shadow: 0 0 10px rgba(0, 217, 245, 0.3);
        }

        /* Add Food Form */
        .add-food-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end; /* Aligns button with inputs */
        }
        .add-food-form .form-group {
            flex: 1 1 150px;
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--admin-accent-glow);
            box-shadow: 0 0 20px rgba(0, 217, 245, 0.3);
        }
        
        .btn-form {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            flex-grow: 1;
            max-width: 200px;
        }
        .btn-primary {
            background: var(--accent-gradient);
            color: #000;
            box-shadow: 0 0 25px rgba(0, 245, 160, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 245, 160, 0.5);
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
                    <a href="users.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span class="link-text">User Management</span>
                    </a>
                    <a href="fooddb.php" class="active">
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
                <h1>Food Database Management</h1>
            </div>

            <!-- DB Error Message -->
            <?php if (isset($dbError)): ?>
                <div class="db-error">
                    <strong>Database Error:</strong> <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Food Section -->
            <section class="glass-card">
                <h3>Add New Food Entry</h3>
                <form method="POST" class="add-food-form">
                    <div class="form-group">
                        <label for="add-name">Name</label>
                        <input id="add-name" name="name" class="input-field" placeholder="e.g., Apple" required/>
                    </div>
                    <div class="form-group">
                        <label for="add-grams">Portion (g)</label>
                        <input id="add-grams" name="grams" type="number" step="1" class="input-field" placeholder="e.g., 100" required/>
                    </div>
                    <div class="form-group">
                        <label for="add-cal">Cal/100g</label>
                        <input id="add-cal" name="cal" type="number" step="0.1" class="input-field" placeholder="e.g., 52" required/>
                    </div>
                    <button name="action" value="add_food" class="btn-form btn primary">Add Food</button>
                </form>
            </section>

            <!-- User Table -->
            <section class="glass-card">
                <h3>Existing Food Items</h3>
                <table class="admin-table" aria-label="Food database items">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Portion (g)</th>
                            <th scope="col">Cal/100g</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($foods)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No food items in database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($foods as $f): ?>
                            <tr>
                                <td data-label="Name">
                                    <form method="POST" style="display:flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="food_id" value="<?= $f['id']?>"/>
                                        <input type="text" name="name" class="table-input" style="width: 150px;" value="<?= htmlspecialchars($f['name']) ?>" placeholder="Name"/>
                                </td>
                                <td data-label="Portion (g)">
                                        <input type="number" name="grams" class="table-input" style="width: 90px;" value="<?= $f['typical_portion_grams'] ?>" placeholder="Portion"/>
                                </td>
                                <td data-label="Cal/100g">
                                        <input type="number" step="0.1" name="cal" class="table-input" style="width: 90px;" value="<?= $f['calories_per_100g'] ?>" placeholder="Cal/100g"/>
                                </td>
                                <td data-label="Actions">
                                        <button name="action" value="edit_food" class="btn-table btn-secondary">Update</button>
                                    </form>
                                    <form method="POST" style="display:inline-block; margin-left: 0.5rem;">
                                        <input type="hidden" name="food_id" value="<?= $f['id'] ?>"/>
                                        <button name="action" value="remove_food" class="btn-table btn-danger">Delete</button>
                                    </form>
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
