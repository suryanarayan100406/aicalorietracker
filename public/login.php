<?php
// This PHP block is kept from your original file
require_once __DIR__ . '/../app/controllers/AuthController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Calorie AI</title>
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
            --sidebar-width: 260px;
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
        
        /* --- NEW: Dashboard Layout --- */
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* --- NEW: Sidebar (Glass) --- */
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
            gap: 3rem;
            z-index: 10;
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

        .logo-icon {
            width: 30px;
            height: 30px;
            background: var(--accent-gradient);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 245, 160, 0.4);
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
        }

        .sidebar-nav a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            opacity: 0.6;
        }
        
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            font-weight: 600;
            opacity: 1;
        }

        /* --- Login Content Area --- */
        .login-content {
            flex-grow: 1;
            padding-left: var(--sidebar-width); /* Offset for sidebar */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .login-form h1 {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .text-gradient {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .input-wrap {
            position: relative;
        }
        
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
            border-color: var(--accent-glow);
            box-shadow: 0 0 20px rgba(0, 245, 160, 0.3);
        }
        
        .btn {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
            font-size: 1rem;
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
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .links a {
            color: var(--accent-glow);
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }
        .links a:hover {
            opacity: 0.8;
        }
        .links span {
            margin: 0 0.5rem;
        }
        
        .error-message {
            background: rgba(255, 50, 100, 0.1);
            border: 1px solid rgba(255, 50, 100, 0.3);
            color: #ff80ab;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .login-wrapper {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                flex-direction: row; /* Horizontal on mobile */
                justify-content: space-between;
                padding: 1.5rem;
            }
            .sidebar-nav {
                display: none; /* Hide nav links on mobile login */
            }
            .login-content {
                padding-left: 0;
                align-items: flex-start;
                padding-top: 4rem;
            }
            .login-form {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="index.php" class="logo">
                <div class="logo-icon"></div>
                CalorieAI
            </a>
            
            <!-- Dummy nav to make it feel like a real app -->
            <nav class="sidebar-nav">
                <span class="sidebar-nav-title">Menu</span>
                <a href="dashboard.php">Dashboard</a>
                <a href="diary.php">My Diary</a>
                <a href="profile.php">Profile</a>
                <a href="login.php" class="active">Log In</a>
            </nav>
        </aside>
        
        <!-- Main Login Form -->
        <main class="login-content">
            <div class="login-form">
                <h1>Welcome <span class="text-gradient">Back</span></h1>
                
                <!-- PHP Error Message Block -->
                <?php if (isset($error) && isset($_POST['type']) && $_POST['type'] === 'user'): ?>
                    <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php endif; ?>

                <form method="post" autocomplete="off" novalidate id="loginForm">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <input required type="text" name="username" id="username" placeholder="e.g., nutrition_ninja" class="input-field" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input required type="password" name="password" id="password" placeholder="••••••••" class="input-field" />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">Log In</button>
                    <input type="hidden" name="type" value="user"/>
                </form>
                
                <div class="links">
                    <a href="register.php">Need an account?</a>
                    <span>|</span>
                    <a href="adminlogin.php">Admin Login</a>
                </div>
            </div>
        </main>
        
    </div>
    
    <script>
        // Simple password toggle script for usability
        // The original file didn't include JS, but this is a small quality-of-life add.
        // We'll remove the complex "hold" logic for a simpler "click" toggle.
        (function() {
            // Find a better selector if the ID from the old file is missing
            const pass = document.getElementById('password');
            const pwReveal = document.getElementById('pwReveal'); // This ID was in your old file

            if (pass && pwReveal) {
                // Change button text for clarity
                pwReveal.textContent = 'Show';
                pwReveal.style.width = 'auto'; // Make width dynamic
                
                pwReveal.addEventListener('click', function() {
                    const isPressed = this.getAttribute('aria-pressed') === 'true';
                    if (isPressed) {
                        // Hide password
                        pass.setAttribute('type', 'password');
                        this.setAttribute('aria-pressed', 'false');
                        this.textContent = 'Show';
                    } else {
                        // Show password
                        pass.setAttribute('type', 'text');
                        this.setAttribute('aria-pressed', 'true');
                        this.textContent = 'Hide';
                    }
                });
            } else if (pwReveal) {
                // If button exists but input doesn't, hide button
                pwReveal.style.display = 'none';
            }
        })();
    </script>

</body>
</html>
