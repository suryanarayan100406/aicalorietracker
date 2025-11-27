<?php 
// This PHP block is kept from your original file
require_once __DIR__ . '/../app/lib/auth.php';
require_once __DIR__ . '/../app/models/User.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Trim inputs and basic validation
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
        $password = isset($_POST['password']) ? $_POST['password']       : '';

        if ($username === '' || $email === '' || $password === '') {
            throw new Exception('Please fill all required fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters.');
        }

        // Optional: if your User model has "findByUsername" / "findByEmail" methods,
        // use them to pre-check duplicates and provide nicer messages.
        if (method_exists('User', 'findByUsername')) {
            $exists = User::findByUsername($username);
            if ($exists) throw new Exception('This username is already taken. Please choose another.');
        }
        if (method_exists('User', 'findByEmail')) {
            $exists = User::findByEmail($email);
            if ($exists) throw new Exception('An account with this email already exists.');
        }

        // Attempt create (User::create should handle hashing etc.)
        $id = User::create($username, $email, $password);
        if (!$id) throw new Exception('Registration failed. Please try again.');

        if (function_exists('regenerate_sess')) regenerate_sess();
        $_SESSION['user_id'] = $id;
        // Good practice: set success flag before redirect (in case you want to show a message)
        $success = true;

        header('Location: dashboard.php');
        exit;
    } catch (\PDOException $e) {
        // Handle DB duplicate / constraint errors gracefully
        // MySQL duplicate message usually: "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'surya' for key 'username'"
        $msg = $e->getMessage();
        if ($e->getCode() == 23000 || stripos($msg, 'Duplicate entry') !== false) {
            // Try to parse the error message
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/i", $msg, $m)) {
                $dupValue = $m[1];
                $keyName  = strtolower($m[2]);

                // MySQL key names can be like 'username', 'email', or 'users.username'
                if (strpos($keyName, 'username') !== false) {
                    $error = 'That username is already taken. Please choose another.';
                } elseif (strpos($keyName, 'email') !== false) {
                    $error = 'That email is already registered. Try logging in or use another email.';
                } else {
                    $error = 'A record with the same value already exists.';
                }
            } else {
                $error = 'A duplicate entry error occurred. Please check your inputs.';
            }
        } else {
            // For other PDO exceptions, log the actual message server-side and show a generic message
            // error_log($e->getMessage()); // uncomment if you want to log
            $error = 'A database error occurred. Please try again later.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Calorie AI</title>
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
        
        /* --- Dashboard Layout --- */
        .register-wrapper {
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
            gap: 3rem;
            z-index: 20; /* keep it on top of content visually */
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

        /* --- Register Content Area --- */
        .register-content {
            flex-grow: 1;
            /* SHIFT the whole content to the right so the fixed sidebar doesn't overlap it */
            margin-left: var(--sidebar-width); /* <-- changed from padding-left to margin-left */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 3rem 2rem;
            z-index: 1;
            position: relative;
        }
        
        /* NEW: Grid for form + aside */
        .register-grid {
            width: 100%;
            max-width: 850px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            align-items: start;
        }

        /* --- Glass Card --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .register-form h1 {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 0.5rem;
        }
        
        .text-gradient {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .lead {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
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
        
        .pw-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            padding: 0.25rem;
        }
        .pw-toggle:hover {
            color: var(--text-primary);
        }
        
        .pw-note {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
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

        /* NOTE: button class updated in HTML to "btn btn-primary" */
        .btn-primary {
            background: var(--accent-gradient);
            color: #000;
            box-shadow: 0 0 25px rgba(0, 245, 160, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 245, 160, 0.5);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.1); 
            color: #fff; 
            backdrop-filter: blur(10px);
            text-align: center;
            margin-top: 1rem;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.15);
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
        
        .success-message {
            background: rgba(0, 245, 160, 0.1);
            border: 1px solid rgba(0, 245, 160, 0.3);
            color: var(--accent-glow);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        /* --- Aside Widget --- */
        .widget {
            /* Use glass-card styles */
            background: rgba(255, 255, 255, 0.02); /* More subtle than form */
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2rem;
        }
        .widget h4 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        .widget ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            color: var(--text-secondary);
        }
        .widget li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .widget li::before {
            content: '✓';
            color: var(--accent-glow);
            font-weight: 800;
            margin-top: 2px;
        }
        
        .legal {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 1.5rem;
        }
        .legal a {
            color: var(--text-primary);
            text-decoration: underline;
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .register-wrapper {
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
                display: none;
            }
            .register-content {
                margin-left: 0;
                align-items: flex-start;
                padding: 2rem 1rem;
            }
            .register-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="register-wrapper">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="index.html" class="logo">
                <div class="logo-icon"></div>
                CalorieAI
            </a>
            
            <!-- Dummy nav to make it feel like a real app -->
            <nav class="sidebar-nav">
                <span class="sidebar-nav-title">Menu</span>
                <a href="dashboard.php">Dashboard</a>
                <a href="diary.php">My Diary</a>
                <a href="login.php">Log In</a>
                <a href="register.php" class="active">Register</a>
            </nav>
        </aside>
        
        <!-- Main Register Content -->
        <main class="register-content">
            <div class="register-grid">
                
                <!-- Column 1: Registration Form -->
                <section class="glass-card register-form" aria-labelledby="register-heading">
                    <h1 id="register-heading">Create your <span class="text-gradient">account</span></h1>
                    <p class="lead">Start tracking your nutrition for free.</p>

                    <!-- PHP Error/Success Messages -->
                    <?php if ($error): ?>
                        <div class="error-message" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message" role="status">Registered successfully — redirecting…</div>
                    <?php endif; ?>

                    <form method="post" autocomplete="off" id="register-form" novalidate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" required aria-required="true" class="input-field" placeholder="Choose a public username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES) : '' ?>" />
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" required aria-required="true" class="input-field" placeholder="you@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES) : '' ?>" />
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrap">
                                <input id="password" name="password" type="password" required aria-required="true" minlength="8" class="input-field" placeholder="••••••••" />
                                <button type="button" class="pw-toggle" id="toggle-pw" aria-pressed="false" aria-label="Show password">Show</button>
                            </div>
                            <div id="pw-hint" class="pw-note">Use at least 8 characters.</div>
                        </div>

                        <!-- fixed class name here so CSS applies -->
                        <button type="submit" class="btn btn-primary" id="submit-btn">Create Account</button>
                        <a href="login.php" class="btn btn-secondary" role="button">Already have an account?</a>

                        <div class="legal">
                            By creating an account, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
                        </div>
                    </form>
                </section>
                
                <!-- Column 2: Aside Widgets -->
                <aside class="side" aria-label="Register sidebar">
                    <div class="widget glass-card" role="region" aria-labelledby="why">
                        <h4 id="why">Why create an account?</h4>
                        <ul>
                            <li>Save and review your meal history.</li>
                            <li>Set goals and track your daily progress.</li>
                            <li>Personalized meal suggestions over time.</li>
                        </ul>
                    </div>
                </aside>
                
            </div> <!-- end .register-grid -->
        </main>
        
    </div> <!-- end .register-wrapper -->
    
    <script>
        // Kept your original JS, it's great!
        (function(){
            const form = document.getElementById('register-form');
            const pw = document.getElementById('password');
            const toggle = document.getElementById('toggle-pw');
            const submitBtn = document.getElementById('submit-btn');
            const pwHint = document.getElementById('pw-hint');

            if (pw && toggle) {
                // Toggle show/hide password
                toggle.addEventListener('click', function(){
                    if (pw.type === 'password') {
                        pw.type = 'text';
                        toggle.textContent = 'Hide';
                        toggle.setAttribute('aria-pressed','true');
                    } else {
                        pw.type = 'password';
                        toggle.textContent = 'Show';
                        toggle.setAttribute('aria-pressed','false');
                    }
                    pw.focus();
                });
            }

            if (pw && pwHint) {
                // Basic client-side validation + strength hint
                pw.addEventListener('input', function(){
                    const val = pw.value || '';
                    let score = 0;
                    if (val.length >= 8) score++;
                    if (/[A-Z]/.test(val)) score++;
                    if (/[0-9]/.test(val)) score++;
                    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val)) score++; // Check for special chars
                    
                    const labels = ['Very weak','Weak','Okay','Strong','Very strong'];
                    const strength = labels[Math.min(score, labels.length-1)];
                    
                    let hintText = `Strength: ${strength}`;
                    if (val.length < 8 && val.length > 0) {
                        hintText = "Must be at least 8 characters.";
                    } else if (val.length == 0) {
                        hintText = "Use at least 8 characters.";
                    }
                    
                    pwHint.textContent = hintText;
                });
            }

            if (form && submitBtn) {
                // Prevent double-submits
                form.addEventListener('submit', function(e){
                    if (!form.checkValidity()) {
                        // Let browser show default validation errors
                        return;
                    }
                    // Disable button on valid submit
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.75';
                    submitBtn.textContent = 'Creating...';
                });
            }
        })();
    </script>

</body>
</html>
