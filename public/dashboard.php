<?php 
require_once __DIR__ . '/../app/lib/auth.php';
require_login(); // ensures session and user context

// Use your DB helper if available
$dbError = null;
$pdo = null;
if (file_exists(__DIR__ . '/../app/lib/db.php')) {
    try {
        require_once __DIR__ . '/../app/lib/db.php';
        if (class_exists('DB') && method_exists('DB', 'conn')) {
            $pdo = DB::conn();
        } else {
            $dbError = 'DB helper found but DB::conn() not available.';
        }
    } catch (Exception $e) {
        $dbError = 'DB connection error: ' . $e->getMessage();
    }
} else {
    $dbError = 'DB helper not found (app/lib/db.php).';
}

// current user id (set by require_login)
$userId = $_SESSION['user_id'] ?? null;

// initialize stats
$todayCalories = 0.0;
$todayProtein = 0.0;
$todayFat = 0.0;
$weekCalories = 0.0;
$goalCalories = null;
$recentMeals = [];

// Only query DB if we have a user and PDO
if ($userId && $pdo) {
    try {
        // 1) Today's totals
        $sqlToday = "
            SELECT
                COALESCE(SUM(calories),0) AS total_cal,
                COALESCE(SUM(JSON_EXTRACT(macros,'$.protein') + 0),0) AS total_protein,
                COALESCE(SUM(JSON_EXTRACT(macros,'$.fat') + 0),0) AS total_fat
            FROM meals
            WHERE user_id = :uid
                AND DATE(created_at) = CURDATE()
        ";
        $stmt = $pdo->prepare($sqlToday);
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        if ($row) {
            $todayCalories = (float)$row['total_cal'];
            $todayProtein  = (float)$row['total_protein'];
            $todayFat      = (float)$row['total_fat'];
        }

        // 2) Weekly calories
        $sqlWeek = "
         SELECT COALESCE(SUM(calories),0) AS week_cal
         FROM meals
         WHERE user_id = :uid
            AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        ";
        $stmt = $pdo->prepare($sqlWeek);
        $stmt->execute([':uid' => $userId]);
        $weekCalories = (float)$stmt->fetchColumn();

        // 3) User goal
        $sqlGoal = "SELECT goal_calories FROM users WHERE id = :uid LIMIT 1";
        $stmt = $pdo->prepare($sqlGoal);
        $stmt->execute([':uid' => $userId]);
        $goalRow = $stmt->fetch();
        if ($goalRow) {
            $goalCalories = $goalRow['goal_calories'] !== null ? (int)$goalRow['goal_calories'] : null;
        }

        // 4) Recent meals
        $sqlRecent = "
         SELECT id, image_path, calories, macros, created_at
         FROM meals
         WHERE user_id = :uid
         ORDER BY created_at DESC
         LIMIT 6
        ";
        $stmt = $pdo->prepare($sqlRecent);
        $stmt->execute([':uid' => $userId]);
        $recentMeals = $stmt->fetchAll();

    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Calorie AI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg-main:#050505; --bg-secondary:#111111; --text-primary:#ffffff; --text-secondary:#a1a1aa;
            --accent-glow:#00f5a0; --accent-gradient:linear-gradient(135deg,#00f5a0,#00d9f5);
            --accent-blue:#00d9f5; --sidebar-width:260px; --max-grid-width:1100px; --sidebar-gap:1rem;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%}
        body{
            font-family:'Inter',system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
            background:var(--bg-main);
            color:var(--text-primary);
            line-height:1.5;overflow-x:hidden;
            background-image:radial-gradient(circle at 10% 20%, rgba(0,245,160,0.12), transparent 40%), radial-gradient(circle at 90% 80%, rgba(0,217,245,0.06), transparent 40%);
        }

        /* Layout */
        .dashboard-wrapper{display:flex;min-height:100vh;width:100%}

        /* Sidebar */
        .sidebar{
            width:var(--sidebar-width);height:100vh;position:fixed;top:0;left:0;
            background:rgba(255,255,255,0.03);border-right:1px solid rgba(255,255,255,0.08);
            backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
            padding:2.25rem;display:flex;flex-direction:column;gap:1.25rem;z-index:999;

            /* scrolling */
            overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;
            padding-inline-end:calc(var(--sidebar-gap));
        }
        .sidebar::-webkit-scrollbar{width:8px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.06);border-radius:999px}
        .sidebar{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.06) transparent}

        .logo{display:flex;align-items:center;gap:.6rem;font-weight:800;font-size:1.15rem;color:var(--text-primary);text-decoration:none}
        .logo-icon{width:34px;height:34px;border-radius:8px;background:var(--accent-gradient);box-shadow:0 0 20px rgba(0,245,160,0.22)}

        .sidebar-nav{display:flex;flex-direction:column;gap:.5rem}
        .sidebar-nav-title{color:var(--text-secondary);font-weight:700;font-size:.78rem;text-transform:uppercase}
        .sidebar-nav a{display:flex;gap:.6rem;align-items:center;padding:.6rem .85rem;border-radius:10px;color:var(--text-secondary);text-decoration:none;font-weight:600;transition:all .18s}
        .sidebar-nav a:hover{background:rgba(255,255,255,0.04);color:var(--text-primary)}
        .sidebar-nav a.active{background:rgba(255,255,255,0.08);color:var(--text-primary)}

        .sidebar .spacer{margin-top:auto;color:var(--text-secondary);font-size:.9rem}

        /* Content area: use margin-left so the main content never sits under the fixed sidebar */
        .dashboard-content{flex:1;margin-left:calc(var(--sidebar-width) + var(--sidebar-gap));padding:2.25rem;min-height:100vh}
        .container{max-width:var(--max-grid-width);margin:0 auto}

        /* header */
        .header-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.75rem}
        .header-bar h1{font-size:1.9rem;font-weight:800}
        .user-id{background:var(--bg-secondary);color:var(--text-secondary);padding:.45rem .8rem;border-radius:10px;border:1px solid rgba(255,255,255,0.06);font-size:.9rem}

        /* cards and grid */
        .glass-card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:1.25rem}
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem}
        .stat-card{padding:1rem;border-radius:12px}
        .stat-card .label{color:var(--text-secondary);font-weight:600;margin-bottom:.35rem}
        .stat-card .value{font-size:1.6rem;font-weight:800}
        .stat-card .value .unit{font-size:.85rem;color:var(--text-secondary);margin-left:.4rem}

        .widget-grid{display:grid;grid-template-columns:2fr 1fr;gap:1rem}
        .progress-bar-outer{height:12px;background:var(--bg-secondary);border-radius:999px;overflow:hidden;border:1px solid rgba(255,255,255,0.04)}
        .progress-bar-inner{height:100%;background:var(--accent-gradient);box-shadow:0 0 12px rgba(0,245,160,0.18);transition:width .5s}
        .progress-text{display:flex;justify-content:space-between;margin-top:.6rem;color:var(--text-secondary);font-weight:600}

        .meals-list{display:flex;flex-direction:column;gap:.75rem;max-height:420px;overflow:auto;padding-right:.2rem}
        .meal-item{display:flex;align-items:center;gap:.75rem;padding:.7rem;background:var(--bg-secondary);border-radius:10px;border:1px solid rgba(255,255,255,0.04)}
        .meal-thumb{width:56px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0}
        .meal-thumb img{width:100%;height:100%;object-fit:cover}
        .meal-info strong{display:block;color:var(--text-primary);font-weight:700}
        .meal-info span{color:var(--text-secondary);font-size:.9rem}
        .meal-calories{font-weight:700;color:var(--accent-glow)}

        .widget-card .value{font-size:1.6rem;font-weight:800}
        .widget-card p{color:var(--text-secondary);margin-top:.5rem}

        .db-error{background:rgba(255,50,100,0.08);border:1px solid rgba(255,50,100,0.2);color:#ff9db8;padding:.9rem;border-radius:10px;margin-bottom:1rem}

        /* Ensure the main content can't be covered by the sidebar on smaller screens */
        @media (max-width:900px){
            .dashboard-wrapper{flex-direction:column}
            .sidebar{position:relative;width:100%;height:auto;border-right:none;border-bottom:1px solid rgba(255,255,255,0.06);flex-direction:row;justify-content:space-between;padding:1rem;gap:.75rem;overflow:visible;padding-inline-end:var(--sidebar-gap)}
            .sidebar-nav{display:none}
            .dashboard-content{margin-left:0;padding:1rem}
            .stats-grid{grid-template-columns:1fr}
            .header-bar{flex-direction:column;align-items:flex-start;gap:.5rem}
        }
        @media (max-width:480px){.header-bar h1{font-size:1.25rem}.meal-thumb{width:48px;height:48px}}

    </style>
</head>
<body>
    
<elevenlabs-convai agent-id="agent_7401k8jrwkwqega8n6ejtvp6x4wd"></elevenlabs-convai><script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
    <div class="dashboard-wrapper">
        <aside class="sidebar" aria-label="Main sidebar">
            <div>
                <a href="index.php" class="logo" aria-label="Calorie AI Home">
                    <div class="logo-icon" aria-hidden="true"></div>
                    <span>CalorieAI</span>
                </a>

                <nav class="sidebar-nav" aria-label="Primary">
                    <div style="margin-top:1.25rem" class="sidebar-nav-title">Menu</div>
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="upload.php">Upload Meal</a>
                    <a href="diary.php">Food Diary</a>
                    <a href="profile.php">Profile & Goals</a>
                </nav>
            </div>

            <div class="spacer">
                <nav class="sidebar-nav user-menu" aria-label="User menu">
                    <a href="login.php?logout=1">Logout</a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-content">
            <div class="container">
                <div class="header-bar">
                    <h1>Dashboard</h1>
                    <div class="user-id" title="Your User ID">User ID: <?= htmlspecialchars($userId) ?></div>
                </div>

                <?php if (!empty($dbError)): ?>
                    <div class="db-error"><strong>Database:</strong> <?= htmlspecialchars($dbError) ?></div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card glass-card">
                        <div class="label">Today's Calories</div>
                        <div class="value text-gradient"><?= number_format($todayCalories, 0) ?></div>
                    </div>
                    <div class="stat-card glass-card">
                        <div class="label">Today's Protein</div>
                        <div class="value"><?= number_format($todayProtein, 0) ?><span class="unit">g</span></div>
                    </div>
                    <div class="stat-card glass-card">
                        <div class="label">Today's Fat</div>
                        <div class="value"><?= number_format($todayFat, 0) ?><span class="unit">g</span></div>
                    </div>
                </div>

                <div class="widget-grid">
                    <div>
                        <section class="glass-card">
                            <h3>Today's Progress</h3>
                            <?php
                                $remaining = ($goalCalories !== null) ? max(0, $goalCalories - $todayCalories) : null;
                                $percent = ($goalCalories && $goalCalories > 0) ? min(100, round(($todayCalories / $goalCalories) * 100)) : 0;
                            ?>

                            <?php if ($goalCalories !== null): ?>
                                <div class="progress-bar-outer" aria-hidden="true">
                                    <div class="progress-bar-inner" style="width: <?= $percent ?>%;"></div>
                                </div>
                                <div class="progress-text">
                                    <span>Goal: <strong><?= number_format($goalCalories) ?> kcal</strong></span>
                                    <span>Remaining: <strong style="color:var(--accent-glow)"><?= number_format($remaining) ?> kcal</strong></span>
                                </div>
                            <?php else: ?>
                                <p style="color:var(--text-secondary)">Set a daily calorie goal in your <a href="profile.php" style="color:var(--accent-glow);font-weight:700">Profile</a> to track progress.</p>
                            <?php endif; ?>
                        </section>

                        <section class="glass-card" style="margin-top:1rem">
                            <h3>Recent Meals</h3>
                            <div class="meals-list" role="list">
                                <?php if (!empty($recentMeals)): ?>
                                    <?php foreach ($recentMeals as $m):
                                        $m_id = (int)$m['id'];
                                        $m_cal = (float)$m['calories'];
                                        $m_macros = is_string($m['macros']) ? json_decode($m['macros'], true) : $m['macros'];
                                        $m_title = $m_macros['label'] ?? ('Meal #' . $m_id);
                                        $m_when = date('M j, H:i', strtotime($m['created_at']));
                                        $thumb = !empty($m['image_path']) ? htmlspecialchars($m['image_path']) : 'https://placehold.co/100x100/111111/a1a1aa?text=Meal';
                                    ?>
                                        <div class="meal-item" role="listitem">
                                            <div class="meal-thumb"><img src="<?= $thumb ?>" alt="<?= htmlspecialchars($m_title) ?>" onerror="this.src='https://placehold.co/100x100/111111/a1a1aa?text=Meal';"></div>
                                            <div class="meal-info">
                                                <strong><?= htmlspecialchars($m_title) ?></strong>
                                                <span><?= htmlspecialchars($m_when) ?></span>
                                            </div>
                                            <div class="meal-calories"><?= number_format($m_cal,0) ?> kcal</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color:var(--text-secondary)">No meals logged yet. <a href="upload.php" style="color:var(--accent-glow);font-weight:700">Upload your first meal</a> to get started.</p>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <div>
                        <section class="widget-card glass-card">
                            <h3>This Week's Total</h3>
                            <div class="value"><?= number_format($weekCalories) ?><span class="unit"> kcal</span></div>
                            <p>Total calories logged since Monday.</p>
                        </section>

                        <section class="widget-card glass-card" style="margin-top:1rem">
                            <h3>Daily Tip</h3>
                            <p>Add a serving of leafy greens — fiber and volume with very few calories.</p>
                        </section>
                    </div>
                </div>
            </div>
        </main>

    </div>

</body>
</html>
