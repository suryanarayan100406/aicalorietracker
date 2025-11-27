<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

// The authentication logic (handling the POST request) would typically be placed here
// before the HTML renders, but we keep the file structure as provided.

// Placeholder for error handling logic if needed:
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && $_POST['type'] === 'admin') {
    $authController = new AuthController();
    // Assuming adminLogin returns an error message string on failure
    $loginError = $authController->adminLogin($_POST['username'], $_POST['password']);
    if ($loginError) {
        $error = $loginError;
    }
    // Note: Successful login should redirect within AuthController.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Calorie AI</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
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
            width: 100%;
            max-width: 850px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.5rem;
            align-items: start;
        }

        /* --- Form Styles --- */
        .login-form h1 {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
            color: var(--admin-accent-glow);
        }
        
        form { margin-top:1rem; display:flex; flex-direction:column; gap:1.5rem; }
        .form-group { display:flex; flex-direction:column; gap:.5rem; }
        label { font-weight:600; color: var(--text-secondary); font-size:.9rem; }
        
        .input-field {
            width: 100%;
            padding: 1rem 1.25rem;
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
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .links a {
            color: var(--admin-accent-glow);
            text-decoration: none;
            font-weight: 600;
        }
        
        /* Side Widgets */
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
            color: var(--admin-accent-glow);
            font-weight: 800;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .err {
            background: rgba(255, 50, 100, 0.1);
            border: 1px solid rgba(255, 50, 100, 0.3);
            color: #ff80ab;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 1.5rem;
            font-weight: 500;
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
                align-items: flex-start;
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
                    <span class="sidebar-nav-title" style="margin-top: 3rem;">Authentication</span>
                    <a href="login.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span class="link-text">User Login</span>
                    </a>
                    <a href="adminlogin.php" class="active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span class="link-text">Admin Login</span>
                    </a>
                </nav>
            </div>
            
            <div>
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span class="link-text">Collapse</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Admin Login Content -->
        <main class="dashboard-content">
        
            <div class="widget-grid">
                
                <!-- Left Column: Form -->
                <section class="glass-card login-form" aria-labelledby="admin-heading">
                    <h1 id="admin-heading">Admin Portal</h1>
                    
                    <?php if (isset($error) && !empty($error) && isset($_POST['type']) && $_POST['type'] === 'admin'): ?>
                        <p class="err" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                    <?php endif; ?>

                    <form method="post" autocomplete="off" novalidate id="adminLoginForm">
                        <div class="form-group">
                            <label for="admin-username">Admin Username</label>
                            <input
                                required
                                type="text"
                                name="username"
                                id="admin-username"
                                class="input-field"
                                placeholder="Enter admin username"
                            />
                        </div>

                        <div class="form-group">
                            <label for="admin-password">Admin Password</label>
                            <input
                                required
                                type="password"
                                name="password"
                                id="admin-password"
                                class="input-field"
                                placeholder="••••••••"
                            />
                        </div>

                        <button type="submit" class="btn primary" id="submitBtn" aria-label="Admin sign in">Secure Sign In</button>
                        <input type="hidden" name="type" value="admin"/>
                    </form>
                    
                    <div class="links">
                        <a href="login.php">Back to User Login</a>
                    </div>
                </section>
                
                <!-- Right Column: Admin Tips -->
                <aside class="side">
                    <div class="widget-card glass-card" role="region" aria-labelledby="admin-tips">
                        <h3 id="admin-tips">Admin Access</h3>
                        <ul>
                            <li>Manage all user accounts.</li>
                            <li>View site-wide usage analytics.</li>
                            <li>Access system configuration.</li>
                            <li>This portal is restricted to authorized personnel.</li>
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

            // --- Form Submit Logic ---
            const form = document.getElementById('adminLoginForm');
            const submitBtn = document.getElementById('submitBtn');

            if (form && submitBtn) {
                // Prevent double-submits
                form.addEventListener('submit', function(e){
                    if (!form.checkValidity()) {
                        return;
                    }
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.75';
                    submitBtn.textContent = 'Signing In...';
                });
            }

            // --- Password Toggle (from original file) ---
            const p = document.getElementById('admin-password');
            if(p){
                p.addEventListener('dblclick', function(){
                    const t = this.getAttribute('type') === 'password' ? 'text' : 'password';
                    this.setAttribute('type', t);
                    // Reverts to password after 3 seconds
                    setTimeout(()=>{ this.setAttribute('type','password') }, 3000);
                });
            }
        })();
    </script>

</body>
</html>

