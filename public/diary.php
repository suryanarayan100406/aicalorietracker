<?php
// diary.php - rewritten full page to match the app theme
require_once __DIR__ . '/../app/controllers/DiaryController.php';
// DiaryController is expected to populate $days (array: 'YYYY-MM-DD' => [mealRows...])
// Optionally $shortcuts may be provided as an array of ['title'=>'','href'=>'']
// We do defensive checks below.

if (!isset($days) || !is_array($days)) {
    $days = []; // ensure $days exists for template logic
}

// Build labels and totals for Chart.js (ordered by date ascending)
$labels = [];
$totals = [];
foreach ($days as $date => $meals) {
    $labels[] = $date;
    $sum = 0;
    if (is_array($meals)) {
        foreach ($meals as $m) {
            $sum += (float)($m['calories'] ?? 0);
        }
    }
    $totals[] = $sum;
}
$weekTotal = array_sum($totals);

// Shortcuts: backend may provide $shortcuts; ensure it's a safe array
if (!isset($shortcuts) || !is_array($shortcuts)) {
    $shortcuts = [];
}

// DB error and User ID from dashboard.php, assuming require_login() populates $_SESSION['user_id']
$dbError = null; // This would be set by your controller logic if an error occurred
$userId = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Diary — Calorie AI</title>
    <!-- Load premium-feel fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- CSS RESET & VARIABLES (Same as theme) --- */
        :root {
            --bg-main: #050505;
            --bg-secondary: #111111;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --accent-glow: #00f5a0; /* Vibrant teal/green glow */
            --accent-gradient: linear-gradient(135deg, #00f5a0, #00d9f5);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 90px; /* New collapsed width */
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
            transition: width 0.3s ease; /* Added transition */
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
        
        .logo .logo-text { /* NEW: Span for text */
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
            flex-shrink: 0; /* Prevent icon from shrinking */
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
        
        .sidebar-nav a .link-text { /* NEW: Span for link text */
            opacity: 1;
            transition: opacity 0.2s ease, width 0.2s ease, margin 0.2s ease;
            white-space: nowrap;
            margin-left: 0;
        }

        .sidebar-nav a svg {
            flex-shrink: 0; /* Prevent icon from shrinking */
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
        
        /* NEW: Toggle Button Style */
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
            flex-shrink: 0; /* Prevent icon from shrinking */
        }

        /* --- Dashboard Content Area --- */
        .dashboard-content {
            flex-grow: 1;
            padding-left: var(--sidebar-width); /* Offset for sidebar */
            min-height: 100vh;
            padding: 3rem;
            transition: padding-left 0.3s ease; /* Added transition */
        }
        
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header-bar h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
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
        
        .btn-ghost {
            background: rgba(255,255,255,0.1); 
            color: #fff; 
            backdrop-filter: blur(10px);
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,0.15);
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
        }
        
        .glass-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
        
        .chart-wrap {
            height: 300px;
            position: relative;
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
        
        /* --- Widget Grid --- */
        .widget-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        
        /* Diary Table */
        .diary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .diary-table thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .diary-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            color: var(--text-primary);
        }
        .diary-table tbody tr:last-child td {
            border-bottom: none;
        }
        .diary-table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }
        
        .meal-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        /* Side Widgets */
        .widget-card .value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
        }
        .widget-card .value .unit {
            font-size: 1.5rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .widget-card p {
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .widget-card ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            color: var(--text-secondary);
        }
        .widget-card li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .widget-card li::before {
            content: '✓';
            color: var(--accent-glow);
            font-weight: 800;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .widget-card .btn-ghost {
            width: 100%;
            margin-top: 0.5rem;
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
        @media (max-width: 1200px) {
            .widget-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 900px) {
            .dashboard-wrapper {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100% !important; /* Override collapsed style */
                height: auto;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                flex-direction: row; /* Horizontal on mobile */
                justify-content: space-between;
                padding: 1.5rem;
                gap: 1rem;
            }
            .sidebar-nav {
                display: none; /* Hide nav links on mobile */
            }
            .sidebar-nav.user-menu {
                display: block; /* Show only user menu */
            }
            .sidebar-toggle {
                display: none; /* Hide toggle on mobile */
            }
            .dashboard-content {
                padding-left: 0 !important; /* Override collapsed style */
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
<elevenlabs-convai agent-id="agent_7401k8jrwkwqega8n6ejtvp6x4wd"></elevenlabs-convai><script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
    <div class="dashboard-wrapper">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <a href="index.html" class="logo">
                    <div class="logo-icon"></div>
                    <span class="logo-text">CalorieAI</span>
                </a>
                
                <nav class="sidebar-nav">
                    <span class="sidebar-nav-title" style="margin-top: 3rem;">Menu</span>
                    <a href="dashboard.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span class="link-text">Dashboard</span>
                    </a>
                    <a href="upload.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><line x1="16" y1="5" x2="22" y2="5"/><line x1="19" y1="2" x2="19" y2="8"/><path d="M3 17.5v-11a2 2 0 0 1 2-2h11.5c.9 0 1.7.5 2.1 1.2l3 5c.4.8.4 1.8 0 2.6l-3 5c-.4.7-1.2 1.2-2.1 1.2H5a2 2 0 0 1-2-2z"/><circle cx="9" cy="12" r="2"/></svg>
                        <span class="link-text">Upload Meal</span>
                    </a>
                    <a href="diary.php" class="active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span class="link-text">Food Diary</span>
                    </a>
                    <a href="profile.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="link-text">Profile & Goals</span>
                    </a>
                </nav>
            </div>
            
            <div>
                <nav class="sidebar-nav user-menu">
                    <a href="login.php?logout=1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="link-text">Logout</span>
                    </a>
                </nav>
                
                <!-- NEW TOGGLE BUTTON -->
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span class="link-text">Collapse</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Dashboard Content -->
        <main class="dashboard-content">
        
            <div class="header-bar">
                <h1>Food Diary</h1>
                <a href="upload.php" class="btn btn-primary">Upload New Meal</a>
            </div>

            <!-- Chart -->
            <section class="glass-card">
                <h3>Calorie History</h3>
                <div class="chart-wrap" role="img" aria-label="Daily calories over time">
                    <canvas id="calChart"></canvas>
                </div>
            </section>
            
            <!-- Main Widget Grid -->
            <div class="widget-grid">
                
                <!-- Left Column: Meal Table -->
                <section class="glass-card">
                    <h3>Logged Meals</h3>
                    <div style="overflow-x: auto;">
                        <table class="diary-table" aria-label="Diary table">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Meals</th>
                                    <th scope="col">Total Calories</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($days)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                            No diary entries yet — upload a meal to get started.
                                        </td>
                                    </tr>
                                <?php else:
                                    foreach ($days as $date => $meals):
                                        $sum = 0;
                                        if (is_array($meals)) {
                                            foreach ($meals as $m) { $sum += (float)($m['calories'] ?? 0); }
                                        }
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($date, ENT_QUOTES) ?></td>
                                        <td>
                                            <?php
                                                $count = 0;
                                                if (is_array($meals)):
                                                    foreach ($meals as $m):
                                                        $count++;
                                                        if ($count > 4) { echo '<span class="meal-badge">+' . (count($meals)-4) . ' more</span>'; break; }
                                                        $name = 'Meal';
                                                        // Simplified name extraction
                                                        if (!empty($m['macros'])) {
                                                            $mac = is_string($m['macros']) ? json_decode($m['macros'], true) : $m['macros'];
                                                            if (is_array($mac) && isset($mac['label'])) $name = $mac['label'];
                                                        } elseif (!empty($m['detected_items'])) {
                                                            $det = json_decode($m['detected_items'], true);
                                                            if (is_array($det) && isset($det[0]['name'])) $name = $det[0]['name'];
                                                        }
                                                        echo '<span class="meal-badge">' . htmlspecialchars($name, ENT_QUOTES) . '</span>';
                                                    endforeach;
                                                endif;
                                            ?>
                                        </td>
                                        <td style="font-weight: 600; color: var(--accent-glow);"><?= number_format($sum, 0) ?> kcal</td>
                                    </tr>
                                <?php
                                    endforeach; // days
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- Right Column: Widgets -->
                <aside class="side">
                    <div class="widget-card glass-card" role="region" aria-labelledby="week-total">
                        <h3 id="week-total">Period Total</h3>
                        <div class="value"><?= number_format($weekTotal, 0) ?><span class="unit">kcal</span></div>
                        <p>Total calories logged in the displayed period.</p>
                    </div>

                    <div class="widget-card glass-card" role="region" aria-labelledby="tips">
                        <h3 id="tips">Diary Tips</h3>
                        <ul>
                            <li>Log meals soon after eating for better accuracy.</li>
                            <li>Use clear photos and a flat surface for best detection.</li>
                        </ul>
                    </div>

                    <!-- Shortcuts (from original file) -->
                    <div class="widget-card glass-card" role="region" aria-labelledby="shortcuts">
                        <h3 id="shortcuts">Shortcuts</h3>
                        <?php if (!empty($shortcuts) && is_array($shortcuts)): ?>
                            <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:.4rem;">
                                <?php foreach ($shortcuts as $sc): 
                                    $title = htmlspecialchars($sc['title'] ?? 'Shortcut', ENT_QUOTES);
                                    $href  = htmlspecialchars($sc['href'] ?? '#', ENT_QUOTES);
                                ?>
                                    <a href="<?= $href ?>" class="btn btn-ghost"><?= $title ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div id="shortcuts-empty" style="margin-top:.4rem;">
                                <p style="color:var(--text-secondary); margin:0 0 .6rem 0;">No shortcuts configured.</p>
                                <div style="display:flex;flex-direction: column; gap:.6rem;flex-wrap:wrap;">
                                    <button id="add-default-shortcuts" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.75rem 1rem;">Add Defaults</button>
                                    <a href="profile.php" class="btn btn-ghost" style="font-size: 0.9rem; padding: 0.75rem 1rem;">Manage Profile</a>
                                </div>
                            </div>
                            <div id="shortcuts-list" style="display:none;flex-direction:column;gap:.6rem;margin-top:.6rem;"></div>
                        <?php endif; ?>
                    </div>
                </aside>

            </div> <!-- end .widget-grid -->
            
        </main>
        
    </div> <!-- end .dashboard-wrapper -->

    <script>
        (function(){
            // --- Chart.js Logic (DARK THEME) ---
            const labels = <?= json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const totals = <?= json_encode($totals, JSON_NUMERIC_CHECK) ?>;
            const canvas = document.getElementById('calChart');
            
            if (canvas) {
                const ctx = canvas.getContext('2d');
                
                // Create gradient
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(0, 245, 160, 0.3)');   /* --accent-glow */
                gradient.addColorStop(1, 'rgba(0, 245, 160, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Calories / day',
                            data: totals,
                            borderColor: 'rgba(0, 245, 160, 1)', /* --accent-glow */
                            backgroundColor: gradient,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: 'rgba(0, 245, 160, 1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: 'rgba(255, 255, 255, 0.7)' } /* --text-secondary */
                            },
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.1)' },
                                ticks: { color: 'rgba(255, 255, 255, 0.7)' } /* --text-secondary */
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                mode:'index', 
                                intersect:false,
                                backgroundColor: 'rgba(5, 5, 5, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: 'rgba(255,255,255,0.1)',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 8,
                            }
                        }
                    }
                });
            }
            
            // --- NEW: Sidebar Toggle Logic ---
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


            // --- Shortcuts Logic (from original file) ---
            <?php if (empty($shortcuts)): ?>
            (function(){
                const btn = document.getElementById('add-default-shortcuts');
                const list = document.getElementById('shortcuts-list');
                const empty = document.getElementById('shortcuts-empty');

                const defaults = [
                    { title: 'Upload Meal', href: 'upload.php' },
                    { title: 'Profile & Goals', href: 'profile.php' },
                    { title: 'Back to Dashboard', href: 'dashboard.php' }
                ];

                function render(arr){
                    list.innerHTML = arr.map(s => '<a href="'+s.href+'" class="btn btn-ghost" style="font-size: 0.9rem; padding: 0.75rem 1rem;">'+s.title+'</a>').join('');
                    if (empty) empty.style.display = 'none';
                    list.style.display = 'flex';
                    try { sessionStorage.setItem('calorie_shortcuts', JSON.stringify(arr)); } catch(e){}
                }

                btn && btn.addEventListener('click', function(e){
                    e.preventDefault();
                    render(defaults);
                });

                // Load saved shortcuts from sessionStorage
                try {
                    const saved = sessionStorage.getItem('calorie_shortcuts');
                    if (saved) {
                        const arr = JSON.parse(saved);
                        if (Array.isArray(arr) && arr.length) render(arr);
                    }
                } catch(e){}
            })();
            <?php endif; ?>

        })();
    </script>
</body>
</html>

