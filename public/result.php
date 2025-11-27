<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/models/Meal.php';
require_login();

// --- PHP Logic from original file ---
$meal = null;
$data = [];
$macros = [];
$error = null;

if (!isset($_GET['mid'])) {
    $error = "No meal ID specified.";
} else {
    try {
        $meal = Meal::find($_GET['mid']);
        if (!$meal) {
            $error = "Meal not found.";
        } else {
            // Ensure data is decoded and defaults to an empty array/object
            $data = $meal['detected_items'] ? json_decode($meal['detected_items'], true) : [];
            $macros = $meal['macros'] ? json_decode($meal['macros'], true) : [];

            // Ensure macros has default values for the chart
            $macros['protein'] = isset($macros['protein']) ? $macros['protein'] : 0;
            $macros['carb']    = isset($macros['carb'])    ? $macros['carb']    : 0;
            $macros['fat']     = isset($macros['fat'])     ? $macros['fat']     : 0;
        }
    } catch (Exception $e) {
        $error = "Error fetching meal data: " . $e->getMessage();
    }
}
// --- End of PHP Logic ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Meal Results — Calorie AI</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

  <!-- Chart.js (CDN) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      --bg-main: #050505;
      --bg-secondary: #111111;
      --text-primary: #ffffff;
      --text-secondary: #a1a1aa;
      --accent-glow: #00f5a0;
      --accent-gradient: linear-gradient(135deg, #00f5a0, #00d9f5);
      --danger-glow: #ff4d80;
      --sidebar-width: 260px;
      --sidebar-width-collapsed: 90px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
      background-color: var(--bg-main);
      color: var(--text-primary);
      font-family: 'Inter', sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
      background-image:
        radial-gradient(circle at 10% 20%, rgba(0,245,160,0.15), transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(0,217,245,0.1), transparent 40%);
      background-repeat: no-repeat;
      background-attachment: fixed;
    }

    /* Layout */
    .dashboard-wrapper { display: flex; width: 100%; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: rgba(255,255,255,0.03);
      border-right: 1px solid rgba(255,255,255,0.08);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      padding: 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      z-index: 10;
      transition: width 0.28s ease;
      overflow-y: auto;
    }

    .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text-primary); font-weight:800; }
    .logo-icon { width: 30px; height: 30px; background: var(--accent-gradient); border-radius: 8px; box-shadow: 0 0 20px rgba(0,245,160,0.28); flex-shrink:0; }
    .logo-text { transition: opacity .18s ease; white-space:nowrap; }

    .sidebar-nav { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
    .sidebar-nav-title { color: var(--text-secondary); font-weight: 600; text-transform: uppercase; font-size: .78rem; }
    .sidebar-nav a { color: var(--text-secondary); text-decoration: none; display:flex; gap:.75rem; align-items:center; padding:.7rem 1rem; border-radius:10px; transition: all .18s ease; white-space:nowrap; }
    .sidebar-nav a:hover { background: rgba(255,255,255,0.05); color: var(--text-primary); }
    .sidebar-nav a.active { background: rgba(255,255,255,0.08); color: var(--text-primary); font-weight:600; }

    .sidebar-toggle {
      background: rgba(255,255,255,0.05);
      border: none;
      color: var(--text-secondary);
      font-weight: 600;
      padding: .7rem 1rem;
      border-radius: 10px;
      cursor: pointer;
      display:flex;
      gap:.6rem;
      align-items:center;
      width: 100%;
      margin-top: 1rem;
      transition: all .18s ease;
    }
    .sidebar-toggle svg { transition: transform .25s ease; flex-shrink:0; }
    .sidebar-toggle[aria-expanded="true"] svg { transform: rotate(180deg); }

    /* collapsed state */
    .dashboard-wrapper.sidebar-collapsed .sidebar { width: var(--sidebar-width-collapsed); }
    .dashboard-wrapper.sidebar-collapsed .dashboard-content { padding-left: var(--sidebar-width-collapsed); }
    .dashboard-wrapper.sidebar-collapsed .logo-text,
    .dashboard-wrapper.sidebar-collapsed .sidebar-nav-title,
    .dashboard-wrapper.sidebar-collapsed .link-text {
      opacity: 0;
      pointer-events: none;
      width: 0;
      overflow: hidden;
      margin-left: 0;
    }
    .dashboard-wrapper.sidebar-collapsed .sidebar-nav a { justify-content: center; padding-left: 0.6rem; padding-right: 0.6rem; }
    .dashboard-wrapper.sidebar-collapsed .logo { justify-content:center; }

    /* Main content */
    .dashboard-content {
      flex-grow: 1;
      padding-left: var(--sidebar-width);
      min-height: 100vh;
      padding: 3rem;
      transition: padding-left 0.28s ease;
    }

    .header-bar { margin-bottom: 2rem; }
    .header-bar h1 { font-size: 2rem; font-weight: 800; }

    .glass-card {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 1.6rem;
      margin-bottom: 1.25rem;
      backdrop-filter: blur(10px);
    }

    .widget-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items:start; }

    .meal-image { width:100%; border-radius:12px; border:1px solid rgba(255,255,255,0.08); margin-bottom:1rem; object-fit:cover; }

    .main-stat { text-align:center; margin-bottom:1.25rem; }
    .main-stat .label { color: var(--text-secondary); margin-bottom: .4rem; display:block; }
    .main-stat .value { font-size:2.75rem; font-weight:800; color: var(--accent-glow); }

    .macros-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:1rem; margin-bottom:1rem; text-align:center; }
    .macro-stat .value { font-size:1.25rem; font-weight:700; }
    .macro-stat .label { font-size:.9rem; color:var(--text-secondary); }

    .chart-wrap { height:200px; max-width:200px; margin: 0 auto 1.25rem auto; position:relative; }

    .btn { padding:.75rem 1rem; border-radius:10px; cursor:pointer; font-weight:600; border:none; display:inline-block; width:100%; text-align:center; }
    .btn-primary { background: var(--accent-gradient); color:#000; box-shadow:0 0 20px rgba(0,245,160,0.18); }
    .btn-ghost { background: rgba(255,255,255,0.06); color:var(--text-primary); }

    .widget-card ul { list-style:none; padding-left:0; margin:0; display:flex; flex-direction:column; gap:.6rem; }
    .widget-card li { background: var(--bg-secondary); padding:.6rem .8rem; border-radius:8px; border:1px solid rgba(255,255,255,0.04); color:var(--text-primary); }

    .err { background: rgba(255,50,100,0.08); border:1px solid rgba(255,50,100,0.2); color:#ff9db8; padding:1rem; border-radius:10px; margin-bottom:1rem; }

    @media (max-width: 900px) {
      .dashboard-wrapper { flex-direction:column; }
      .sidebar { position:relative; width:100% !important; height:auto; border-right:none; border-bottom:1px solid rgba(255,255,255,0.06); flex-direction:row; justify-content:space-between; padding:1rem; }
      .sidebar-nav { display:none; }
      .sidebar-toggle { display: none; }
      .dashboard-content { padding-left: 0 !important; padding: 1.25rem; }
      .widget-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="dashboard-wrapper" id="dashboardWrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Main sidebar">
      <div>
        <a href="index.html" class="logo" aria-label="Calorie AI Home">
          <div class="logo-icon" aria-hidden="true"></div>
          <span class="logo-text">CalorieAI</span>
        </a>

        <nav class="sidebar-nav" aria-label="Primary navigation">
          <div class="sidebar-nav-title" style="margin-top:1.25rem">Menu</div>

          <a href="dashboard.php" class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            <span class="link-text">Dashboard</span>
          </a>

          <a href="upload.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/></svg>
            <span class="link-text">Upload Meal</span>
          </a>

          <a href="diary.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/></svg>
            <span class="link-text">Food Diary</span>
          </a>

          <a href="profile.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
            <span class="link-text">Profile & Goals</span>
          </a>
        </nav>
      </div>

      <div>
        <nav class="sidebar-nav user-menu" aria-label="User menu">
          <a href="login.php?logout=1">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg>
            <span class="link-text">Logout</span>
          </a>
        </nav>

        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="false">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="15 18 9 12 15 6"></polyline></svg>
          <span class="link-text">Collapse</span>
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="dashboard-content" id="mainContent" role="main">
      <div class="header-bar">
        <h1 id="upload-heading">Meal <span style="background: var(--accent-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Results</span></h1>
      </div>

      <?php if ($error): ?>
        <div class="err" role="alert">
          <?= htmlspecialchars($error, ENT_QUOTES) ?>
          <div style="margin-top:1rem;"><a href="dashboard.php" class="btn btn-ghost" style="width:auto;">Back to Dashboard</a></div>
        </div>
      <?php elseif ($meal): ?>

        <div class="widget-grid" aria-live="polite">
          <!-- Left -->
          <section class="glass-card" aria-labelledby="meal-heading">
            <h2 id="meal-heading" style="margin-top:0; margin-bottom:0.75rem; font-size:1.1rem;">Meal Summary</h2>

            <img src="<?= htmlspecialchars($meal['image_path'] ?? '', ENT_QUOTES) ?>" alt="Meal image" class="meal-image" onerror="this.src='https://placehold.co/600x400/111111/a1a1aa?text=Meal+Image';">

            <div class="main-stat">
              <span class="label">Estimated Total</span>
              <div class="value"><?= round($meal['calories'] ?? 0, 1) ?> <small style="font-size:0.5em; color:var(--text-secondary)">kcal</small></div>
            </div>

            <div class="chart-wrap" role="img" aria-label="Macro breakdown">
              <canvas id="macroChart" aria-hidden="false"></canvas>
            </div>

            <div class="macros-grid" role="list">
              <div class="macro-stat" role="listitem">
                <div class="value" style="color:#00f5a0;"><?= htmlspecialchars($macros['protein'], ENT_QUOTES) ?><span class="unit">g</span></div>
                <div class="label">Protein</div>
              </div>
              <div class="macro-stat" role="listitem">
                <div class="value" style="color:#00d9f5;"><?= htmlspecialchars($macros['carb'], ENT_QUOTES) ?><span class="unit">g</span></div>
                <div class="label">Carbs</div>
              </div>
              <div class="macro-stat" role="listitem">
                <div class="value" style="color:#ff70a6;"><?= htmlspecialchars($macros['fat'], ENT_QUOTES) ?><span class="unit">g</span></div>
                <div class="label">Fat</div>
              </div>
            </div>

            <form method="POST" action="diary.php" style="margin-top:.6rem;">
              <input type="hidden" name="meal_id" value="<?= htmlspecialchars($meal['id'] ?? 0, ENT_QUOTES) ?>">
              <button type="submit" class="btn btn-primary">Add to Diary</button>
            </form>

            <div style="margin-top:.8rem; display:grid; grid-template-columns:1fr 1fr; gap:.6rem;">
              <button type="button" class="btn btn-ghost" onclick="rateMeal('accurate')">Accurate</button>
              <button type="button" class="btn btn-ghost" onclick="rateMeal('inaccurate')">Inaccurate</button>
            </div>
          </section>

          <!-- Right -->
          <aside class="side">
            <div class="widget-card glass-card" role="region" aria-labelledby="detected-items">
              <h3 id="detected-items">Detected Items</h3>
              <?php if (!empty($data)): ?>
                <ul>
                  <?php foreach ($data as $item): ?>
                    <li><?= htmlspecialchars($item['name'] ?? 'Item', ENT_QUOTES) ?> (<?= htmlspecialchars($item['grams'] ?? 'N/A', ENT_QUOTES) ?>g)</li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p>No individual items were detected.</p>
              <?php endif; ?>
            </div>

            <div class="widget-card glass-card">
              <h3>Details</h3>
              <p><strong>Confidence:</strong> <?= isset($meal['confidence']) ? round($meal['confidence']*100,1) . '%' : 'N/A' ?></p>
              <p><strong>Sustainability Score:</strong> <?= htmlspecialchars($meal['waste_score'] ?? '—', ENT_QUOTES) ?>/100</p>
            </div>

            <div class="widget-card glass-card">
              <h3>AI Suggestions</h3>
              <p class="suggestion"><strong>Balance Suggestion:</strong> <?= htmlspecialchars($meal['suggestion'] ?? 'No suggestions available.', ENT_QUOTES) ?></p>

              <?php if (!empty($meal['is_leftover']) && !empty($meal['recipe'])): ?>
                <p class="suggestion" style="margin-top:1rem"><strong style="color:#ff70a6">Leftover Recipe Idea:</strong> <?= htmlspecialchars($meal['recipe'], ENT_QUOTES) ?></p>
              <?php endif; ?>
            </div>
          </aside>
        </div>

      <?php endif; ?>
    </main>
  </div>

  <script>
  (function () {
    'use strict';

    // safely read server-side values
    const MEAL_ID = <?= json_encode($meal['id'] ?? 0) ?>;
    const AB_GROUP = <?= json_encode($meal['ab_group'] ?? 'A') ?>;
    const MACROS = <?= json_encode([
      isset($macros['protein']) ? $macros['protein'] : 0,
      isset($macros['carb']) ? $macros['carb'] : 0,
      isset($macros['fat']) ? $macros['fat'] : 0
    ]) ?>;

    // Sidebar toggle logic (robust + accessible + persistent)
    (function sidebarToggle() {
      const wrapper = document.getElementById('dashboardWrapper');
      const toggleBtn = document.getElementById('sidebarToggle');
      const stateKey = 'calorie-ai-sidebar-collapsed';

      if (!wrapper || !toggleBtn) return;

      const setState = (collapsed) => {
        wrapper.classList.toggle('sidebar-collapsed', !!collapsed);
        toggleBtn.setAttribute('aria-expanded', !!collapsed ? 'true' : 'false');
        // update label text for clarity
        const label = toggleBtn.querySelector('.link-text');
        if (label) label.textContent = collapsed ? 'Expand' : 'Collapse';
        localStorage.setItem(stateKey, !!collapsed ? 'true' : 'false');
      };

      // init from storage
      const stored = localStorage.getItem(stateKey);
      const initCollapsed = stored === 'true';
      setState(initCollapsed);

      toggleBtn.addEventListener('click', function (e) {
        const isCollapsedNow = wrapper.classList.contains('sidebar-collapsed');
        setState(!isCollapsedNow);
      });
    })();

    // rateMeal: POST feedback
    window.rateMeal = function (rating) {
      if (!MEAL_ID) {
        alert('Meal id missing.');
        return;
      }
      const body = new URLSearchParams({
        action: 'rate_meal',
        meal_id: String(MEAL_ID),
        rating: String(rating),
        ab_group: String(AB_GROUP)
      }).toString();

      fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
      }).then(r => {
        if (!r.ok) throw new Error('Network response not ok');
        return r.json();
      }).then(json => {
        // Basic UX: replace with nicer UI later
        alert('Thanks for your feedback!');
      }).catch(err => {
        console.error(err);
        alert('Failed to submit feedback — try again later.');
      });
    };

    // Chart: macro donut (guarded)
    (function renderMacroChart() {
      const canvas = document.getElementById('macroChart');
      if (!canvas) return;
      if (typeof Chart === 'undefined') {
        // Chart.js not loaded — gracefully exit
        console.warn('Chart.js missing; macro chart will not render.');
        return;
      }

      // read CSS variable --bg-secondary as fallback border color
      let cssBg = getComputedStyle(document.documentElement).getPropertyValue('--bg-secondary') || '#111111';
      cssBg = cssBg.trim() || '#111111';

      const ctx = canvas.getContext('2d');

      const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Protein', 'Carbs', 'Fat'],
          datasets: [{
            data: MACROS,
            backgroundColor: [
              'rgba(0,245,160,0.85)',
              'rgba(0,217,245,0.85)',
              'rgba(255,112,166,0.85)'
            ],
            borderColor: cssBg,
            borderWidth: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '70%',
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (ctx) {
                  const label = ctx.label || '';
                  const value = ctx.raw || 0;
                  return ` ${label}: ${value}g`;
                }
              }
            }
          }
        }
      });
    })();

  })();
  </script>
</body>
</html>
