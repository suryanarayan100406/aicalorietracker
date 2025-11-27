<?php
// ... (your existing PHP database connection code)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard - CalorieVision</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Outfit:wght@500;700&display=swap" rel="stylesheet" />
<style>
  body {
    font-family: 'Outfit', 'Inter', sans-serif;
    margin: 0;
    min-height: 100vh;
    background:
      linear-gradient(135deg, #1d1f20 0%, #222f37 100%) fixed,
      url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
    color: #e6f3fa;
    position: relative;
    overflow-x: hidden;
    perspective: 1300px;
  }
  body::before {
    content: '';
    position: fixed;
    z-index: 0;
    inset: 0;
    background: rgba(22,32,54,0.5);
    backdrop-filter: blur(6px);
  }
  header {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(90deg, #51e2f5 30%, #7de2fc 80%);
    color: #151c22;
    padding: 1.5rem 2.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 48px rgba(110,215,255,0.48);
    border-bottom-left-radius: 36px 18px;
    transform-style: preserve-3d;
  }
  header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.2rem;
    letter-spacing: -1px;
    text-shadow: 0 4px 36px rgba(30,160,180,0.38);
    filter: drop-shadow(0 4px 12px #e0feff60);
    transform: translateZ(30px);
    transform-style: preserve-3d;
  }
  header a {
    color: #293c44;
    text-decoration: none;
    font-weight: 500;
    font-size: 1.1rem;
    padding: 0.55em 1.4em;
    border-radius: 1.5em;
    background: linear-gradient(90deg,#e6f3fa,#b4ffd6 70%);
    box-shadow: 0 6px 24px #8bf5f384, 0 3px 8px #45e0dd44;
    transition: background 0.3s ease, box-shadow 0.3s ease;
    transform: translateZ(15px);
  }
  header a:hover {
    background: linear-gradient(90deg,#a6eaff,#5fffb6 70%);
    color: #0c0e0e;
    box-shadow: 0 8px 36px #76f2d473, 0 5px 14px #45e0dd66;
  }
  .dashboard-container {
    display: flex;
    position: relative;
    transform-style: preserve-3d;
  }
  nav {
    width: 250px;
    background: rgba(26,39,48,0.8);
    min-height: 90vh;
    box-shadow:
      6px 14px 52px rgba(89, 232, 196, 0.3),
      inset 0 0 30px rgba(0, 255, 230, 0.14);
    padding: 3.5rem 0.5rem 2rem 0.2rem;
    border-top-right-radius: 44px 28px;
    border-bottom-right-radius: 38px 20px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    z-index: 2;
    transform-style: preserve-3d;
    transform: translateZ(20px);
  }
  nav a {
    display: block;
    margin: 0.4rem 0.9rem;
    padding: 1.5rem 1.1rem 1.5rem 2.2rem;
    color: #e6f3fa;
    font-size: 1.3rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    border-radius: 1.8em;
    background: linear-gradient(95deg, #1a3849 60%, #19ead9 100%);
    box-shadow:
      0 4px 16px 0 #20f2dfbb,
      inset 0 0 20px rgba(7, 215, 203, 0.65);
    border: none;
    transition: filter 0.3s, background 0.45s, color 0.3s;
    outline: none;
    filter: drop-shadow(0 4px 14px #0bded9dd);
    transform-style: preserve-3d;
  }
  nav a:hover,
  nav a:focus {
    background: linear-gradient(90deg, #0bf9cd 20%, #17eec4 90%);
    color: #0d1a1c;
    font-weight: 700;
    filter: brightness(1.15) drop-shadow(0 8px 24px #1cf6dbce);
    transform: translateZ(10px);
  }
  main {
    flex: 1 1 0;
    padding: 3.8rem 5% 3rem 5%;
    min-width: 0;
    position: relative;
    z-index: 1;
    transform-style: preserve-3d;
  }
  main h2 {
    margin-top: 0;
    margin-bottom: 3rem;
    font-size: 2.8rem;
    font-weight: 800;
    letter-spacing: -0.035em;
    text-shadow: 0 2px 22px #29e1f7a0;
    transform: translateZ(30px);
  }
  .stats-container {
    display: flex;
    gap: 3.8rem;
    flex-wrap: wrap;
    transform-style: preserve-3d;
  }
  .stat-card {
    background: rgba(17, 32, 52, 0.85);
    border-radius: 2.5rem;
    box-shadow:
      0 10px 38px #23edd945,
      inset 0 0 28px #0af5d559,
      0 4px 18px #0af5d588;
    padding: 3rem 2.8rem;
    min-width: 260px;
    max-width: 370px;
    flex: 1 1 280px;
    margin: 0 0 2rem 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.35s ease, box-shadow 0.3s ease;
    cursor: default;
  }
  .stat-card:hover {
    transform: translateY(-7px) translateZ(20px) scale(1.04);
    box-shadow:
      0 12px 52px #17fffdbb,
      inset 0 0 38px #12fce199,
      0 6px 26px #11fdddcc;
    z-index: 11;
  }
  .stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(
      130deg,
      rgba(255, 255, 255, 0.24) 0%,
      rgba(0, 0, 0, 0.08) 40%,
      rgba(255, 255, 255, 0.14) 100%
    );
    backdrop-filter: blur(5px);
    border-radius: inherit;
    transform-style: preserve-3d;
  }
  .stat-card h3 {
    color: #a7f9ff;
    font-size: 1.35rem;
    font-weight: 700;
    z-index: 1;
    position: relative;
    margin-bottom: 1.4rem;
    letter-spacing: 0.04em;
    user-select: none;
    text-shadow:
      0 0 8px #81f8ffcc,
      0 3px 16px #69e9ffbb;
    transform-style: preserve-3d;
  }
  .stat-card p {
    font-size: 3rem;
    font-weight: 900;
    color: #e0fff9;
    margin: 0;
    letter-spacing: 0.06em;
    text-shadow:
      0 4px 38px #3efffdbb,
      0 8px 24px #2bfffbaa;
    z-index: 1;
    position: relative;
    transform-style: preserve-3d;
  }
  .stat-card .icon3d {
    position: absolute;
    top: -20px;
    right: -30px;
    font-size: 6.5rem;
    opacity: 0.19;
    z-index: 0;
    transform: rotate(-28deg) translateZ(15px);
    filter: blur(0.9px);
    pointer-events: none;
    user-select: none;
  }
  .decor-bg {
    position: absolute;
    z-index: -2;
    width: 64vw;
    max-width: 900px;
    top: 12vh;
    left: 26vw;
    pointer-events: none;
    opacity: 0.18;
    transform-style: preserve-3d;
    transform: translateZ(-95px);
  }
  @media (max-width: 1000px) {
    main {
      padding: 2.5rem 3% 3rem 3%;
    }
    .stats-container {
      gap: 1.6rem;
    }
  }
  @media (max-width: 768px) {
    .dashboard-container {
      flex-direction: column;
    }
    nav {
      width: 100vw;
      min-height: unset;
      flex-direction: row;
      justify-content: space-evenly;
      border-radius: 0 0 32px 32px;
      padding: 0.6rem 0;
      gap: 1rem;
      background: rgba(17, 35, 51, 0.84);
      filter: drop-shadow(0 4px 16px #05f6d4d5);
      transform-style: preserve-3d;
    }
    nav a {
      margin: 0.6rem auto;
      font-size: 1.15rem;
      padding: 1.3rem 0.9rem 1.2rem 1rem;
      border-radius: 1.7em;
    }
  }
</style>
</head>
<body>
<svg class="decor-bg" viewBox="0 0 500 400" fill="none" aria-hidden="true" focusable="false">
  <ellipse opacity="0.62" cx="330" cy="170" rx="200" ry="120" fill="#00f6FF" />
  <ellipse opacity="0.19" cx="210" cy="250" rx="120" ry="95" fill="#34FFB6" />
  <ellipse opacity="0.19" cx="410" cy="300" rx="140" ry="75" fill="#00FFC7" />
</svg>
<header>
  <h1>CalorieVision Admin</h1>
  <div>
    <a href="../login.php">Logout</a>
  </div>
</header>
<div class="dashboard-container">
  <nav>
    <a href="users.php">👥 User Management</a>
    <a href="fooddb.php">🥗 Food DB</a>
  </nav>
  <main>
    <h2>Dashboard Overview</h2>
    <div class="stats-container">
      <div class="stat-card">
        <div class="icon3d" aria-hidden="true">👤</div>
        <h3>Total Users</h3>
        <p>
          <?php
          try {
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $userCount = $stmt->fetchColumn();
            echo htmlspecialchars($userCount !== false ? $userCount : 0, ENT_QUOTES, 'UTF-8');
          } catch (PDOException $e) {
            echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
          }
          ?>
        </p>
      </div>
      <div class="stat-card">
        <div class="icon3d" aria-hidden="true">📷</div>
        <h3>Uploads (24H)</h3>
        <p>
          <?php
          try {
            $stmt = $pdo->query('SELECT COUNT(*) FROM meals WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
            $uploadCount = $stmt->fetchColumn();
            echo htmlspecialchars($uploadCount !== false ? $uploadCount : 0, ENT_QUOTES, 'UTF-8');
          } catch (PDOException $e) {
            echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
          }
          ?>
        </p>
      </div>
      <div class="stat-card">
        <div class="icon3d" aria-hidden="true">📊</div>
        <h3>Other Stats</h3>
        <p>--</p>
      </div>
    </div>
  </main>
</div>
</body>
</html>
