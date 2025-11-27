<?php
// This PHP block is kept from your original file
require_once __DIR__ . '/../app/controllers/ProfileController.php';
// ProfileController should provide $user (assoc array) and may provide $todayCalories (float)
$user = $user ?? [];
$todayCalories = isset($todayCalories) ? (float)$todayCalories : 0;
$error = $error ?? null; // Ensure $error exists
$success = $success ?? null; // Ensure $success exists
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Goals — Calorie AI</title>
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
            --danger-glow: #ff4d80; /* Neon pink/red for errors */
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
        }
        
        .header-bar .lead {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        .text-gradient {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
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
        
        /* --- Widget Grid --- */
        .widget-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        /* --- Form Styles --- */
        form { margin-top:1rem; display:flex; flex-direction:column; gap:1.5rem; }
        .form-group { display:flex; flex-direction:column; gap:.5rem; }
        label { font-weight:600; color: var(--text-secondary); font-size:.9rem; }
        
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 1rem 1.25rem;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        input[type="number"]:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--accent-glow);
            box-shadow: 0 0 20px rgba(0, 245, 160, 0.3);
        }
        
        /* Progress Preview */
        .goal-preview {
            padding: 1.5rem;
            border-radius: 12px;
            background: var(--bg-secondary);
            border:1px solid rgba(255,255,255,0.05);
        }
        .progress {
            width:100%;
            height:12px;
            background: rgba(0,0,0,0.3);
            border-radius:8px;
            overflow:hidden;
        }
        .progress .bar {
            height:100%;
            width:0%;
            background: var(--accent-gradient);
            box-shadow: 0 0 15px var(--accent-glow);
            transition: width .28s ease;
        }
        .muted { color: var(--text-secondary); font-size:.94rem; }
        
        /* Button Actions */
        .actions { display:flex; gap:.8rem; margin-top:1.5rem; align-items:center; }
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
            font-size: 1rem;
        }
        .btn.primary { background: var(--accent-gradient); color: #000; box-shadow: 0 0 25px rgba(0, 245, 160, 0.3); }
        .btn.primary:hover { transform: translateY(-2px); box-shadow: 0 0 40px rgba(0, 245, 160, 0.5); }
        .btn.ghost { background: rgba(255,255,255,0.1); color: #fff; backdrop-filter: blur(10px); }
        .btn.ghost:hover { background: rgba(255,255,255,0.15); }
        
        /* Side Widgets */
        .widget-card .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }
        .widget-card .stat-label {
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .widget-card .divider {
            height: 1px;
            margin: 1.5rem 0;
            background: rgba(255,255,255,0.1);
        }
        .widget-card ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            color: var(--text-secondary);
            padding-left: 0;
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
        
        .err, .success {
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 1.5rem;
            font-weight: 500;
        }
        .err {
            background: rgba(255, 50, 100, 0.1);
            border: 1px solid rgba(255, 50, 100, 0.3);
            color: #ff80ab;
        }
        .success {
            background: rgba(0, 245, 160, 0.1);
            border: 1px solid rgba(0, 245, 160, 0.3);
            color: var(--accent-glow);
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
        @media (max-width: 720px) {
            .actions {
                flex-direction: column;
            }
            .actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

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
                    <a href="diary.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span class="link-text">Food Diary</span>
                    </a>
                    <a href="profile.php" class="active">
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
                
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span class="link-text">Collapse</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Dashboard Content -->
        <main class="dashboard-content">
        
            <div class="header-bar">
                <div>
                    <h1 id="profile-heading">Profile & <span class="text-gradient">Goals</span></h1>
                    <p class="lead">Update your weight and daily targets to personalize suggestions.</p>
                </div>
            </div>

            <!-- Main Widget Grid -->
            <div class="widget-grid">
                
                <!-- Left Column: Form -->
                <section class="glass-card" aria-labelledby="profile-heading">
                    
                    <?php if (isset($success) && $success): ?>
                        <div class="success" role="status">Saved successfully.</div>
                    <?php endif; ?>
                    <?php if (isset($error) && $error): ?>
                        <div class="err" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input
                                type="number"
                                step="0.1"
                                name="weight"
                                id="weight"
                                class="input-field"
                                value="<?= htmlspecialchars($user['weight'] ?? '', ENT_QUOTES) ?>"
                                placeholder="Your current weight"
                                aria-label="Weight in kilograms"
                            />
                        </div>

                        <div class="form-group">
                            <label for="goal_calories">Target Calories (kcal/day)</label>
                            <input
                                type="number"
                                name="goal_calories"
                                id="goal_calories"
                                class="input-field"
                                value="<?= htmlspecialchars($user['goal_calories'] ?? '', ENT_QUOTES) ?>"
                                placeholder="e.g., 2000"
                                aria-label="Daily calorie goal"
                            />
                        </div>

                        <!-- Progress Preview -->
                        <div class="goal-preview" aria-live="polite">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div style="font-size:.95rem;color:var(--text-primary);font-weight:700;">Today's Progress Preview</div>
                                <div id="progress-numbers" class="muted"><?= number_format($todayCalories,0) ?> / <?= htmlspecialchars($user['goal_calories'] ?? '—', ENT_QUOTES) ?> kcal</div>
                            </div>
                            <div style="margin-top:.6rem;">
                                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?= max(1, (int)($user['goal_calories'] ?? 1)) ?>" aria-valuenow="<?= number_format(min($todayCalories, (int)($user['goal_calories'] ?? 0)),0) ?>">
                                    <div class="bar" id="goal-bar" style="width:0%"></div>
                                </div>
                                <div style="margin-top:.5rem;" id="goal-note" class="muted">Adjust your goal to see live preview.</div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn primary" aria-label="Save profile">Save Changes</button>
                            <a href="dashboard.php" class="btn ghost" aria-label="Back to dashboard">Back to dashboard</a>
                        </div>
                    </form>
                </section>
                
                <!-- Right Column: Widgets -->
                <aside class="side">
                    <div class="widget-card glass-card" role="region" aria-labelledby="quick-stats">
                        <h3 id="quick-stats">Quick Stats</h3>
                        <div>
                            <div class="stat-value"><?= htmlspecialchars($user['weight'] ?? '—', ENT_QUOTES) ?><span class="unit"> kg</span></div>
                            <div class="stat-label">Current Weight</div>
                        </div>
                        <div class="divider"></div>
                        <div>
                            <div class="stat-value"><?= htmlspecialchars($user['goal_calories'] ?? '—', ENT_QUOTES) ?><span class="unit"> kcal</span></div>
                            <div class="stat-label">Daily Goal</div>
                        </div>
                    </div>

                    <div class="widget-card glass-card" role="region" aria-labelledby="profile-tips">
                        <h3 id="profile-tips">Tips</h3>
                        <ul>
                            <li>Update weight weekly to keep recommendations accurate.</li>
                            <li>Set a realistic calorie target — small consistent changes win.</li>
                            <li>Use the diary and upload features to track trends over time.</li>
                        </ul>
                    </div>
                </aside>
                
            </div> <!-- end .widget-grid -->
            
        </main>
        
    </div> <!-- end .dashboard-wrapper -->

    <script>
        (function(){
            // --- Sidebar Toggle Logic ---
            const wrapper = document.querySelector('.dashboard-wrapper');
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebarStateKey = 'calorie-ai-sidebar-collapsed';

            if (wrapper && toggleBtn) {
                let isCollapsed = localStorage.getItem(sidebarStateKey) === 'true';
                if (isCollapsed) {
                    wrapper.classList.add('sidebar-collapsed');
                }
                toggleBtn.addEventListener('click', function() {
                    isCollapsed = !isCollapsed;
                    wrapper.classList.toggle('sidebar-collapsed', isCollapsed);
                    localStorage.setItem(sidebarStateKey, isCollapsed);
                });
            }

            // --- Progress Bar Logic (from original file) ---
            const todayCalories = <?= json_encode($todayCalories, JSON_NUMERIC_CHECK) ?>;
            const goalInput = document.getElementById('goal_calories');
            const bar = document.getElementById('goal-bar');
            const progressNumbers = document.getElementById('progress-numbers');
            const goalNote = document.getElementById('goal-note');

            function updateProgress() {
                let goal = parseInt(goalInput.value, 10);
                if (!goal || goal <= 0) {
                    bar.style.width = '0%';
                    progressNumbers.textContent = todayCalories.toFixed(0) + ' / — kcal';
                    goalNote.textContent = 'Set a daily calorie goal to see your progress preview.';
                    return;
                }
                const pct = Math.min(100, Math.round((todayCalories / goal) * 100));
                bar.style.width = pct + '%';
                progressNumbers.textContent = todayCalories.toFixed(0) + ' / ' + goal + ' kcal';
                if (pct >= 100) {
                    goalNote.textContent = 'You reached or exceeded your goal today!';
                } else {
                    goalNote.textContent = pct + '% of goal reached today.';
                }
            }

            goalInput && goalInput.addEventListener('input', updateProgress);
            // Run on load to set initial state
            updateProgress();
        })();
    </script>
</body>
</html>
