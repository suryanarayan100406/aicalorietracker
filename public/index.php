<!DOCTYPE html>  
<html lang="en">
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5610879813970226"

     crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calorie AI — Next-Gen Nutrition Tracker</title>
    <!-- Load premium-feel fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        /* --- CSS RESET & VARIABLES --- */
        :root {
            --bg-main: #050505;
            --bg-secondary: #111111;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --accent-glow: #00f5a0; /* Vibrant teal/green glow */
            --accent-gradient: linear-gradient(135deg, #00f5a0, #00d9f5);
            --container-width: 1100px;
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
            background-image: radial-gradient(circle at 50% 0%, rgba(0, 245, 160, 0.15), transparent 40%);
            background-repeat: no-repeat;
        }

        /* --- UTILITIES --- */
        .container {
            width: 90%;
            max-width: var(--container-width);
            margin: 0 auto;
        }

        .text-gradient {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        /* --- NAVIGATION --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(5, 5, 5, 0.8);
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

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* only non-button nav links use the secondary color */
        .nav-links a:not(.btn) {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            padding: 0.5rem 0.5rem;
        }

        .nav-links a:not(.btn):hover {
            color: var(--text-primary);
        }
        
        .nav-links a.active {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* ensure buttons inside the nav (like See Demo) use black text */
        .nav-links .btn {
            color: #000;
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
        
        /* Mobile Nav Toggle */
        .mobile-toggle {
            display: none;
            cursor: pointer;
            width: 24px;
            height: 24px;
            flex-direction: column;
            justify-content: space-around;
        }
        .mobile-toggle span {
            width: 100%;
            height: 2px;
            background: var(--text-primary);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* --- HERO SECTION --- */
        .hero {
            padding: 8rem 0;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: clamp(3rem, 7vw, 4.5rem); /* Responsive huge text */
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto 3rem auto;
        }

        /* --- GLASS CARDS --- */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 4rem 0;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .glass-card h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .glass-card p {
            color: var(--text-secondary);
        }

        /* --- FOOTER --- */
        footer {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 3rem 0;
            text-align: center;
            margin-top: 5rem;
            color: var(--text-secondary);
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .mobile-toggle {
                display: flex;
            }
            .hero {
                padding: 5rem 0;
            }
            .hero h1 {
                font-size: 2.5rem;
            }
            .hero p {
                font-size: 1rem;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .glass-card {
                padding: 2rem;
            }
        }

    </style>
</head>
<body>
<elevenlabs-convai agent-id="agent_7401k8jrwkwqega8n6ejtvp6x4wd"></elevenlabs-convai><script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
    <!-- Navigation -->
    <nav>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.html" class="logo">
                <div class="logo-icon"></div>
                CalorieAI
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="pricing.html">Pricing</a>
                <a href="login.php">Login</a>
                <!-- Reverted style and changed text to one short word -->
                <a href="register.php" class="btn btn-primary" style="margin-left: 1rem;">See Demo</a>
            </div>
            <!-- Mobile Menu Button -->
            <div class="mobile-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div style="display: inline-block; padding: 0.5rem 1rem; background: rgba(0, 245, 160, 0.1); border-radius: 30px; color: var(--accent-glow); font-weight: 600; font-size: 0.9rem; margin-bottom: 1.5rem; border: 1px solid rgba(0, 245, 160, 0.2);">
                ✨New: AI Voice Logging Added
            </div>
            <h1>
                Nutrition tracking,<br>
                <span class="text-gradient">reinvented with AI.</span>
            </h1>
            <p>Stop searching databases. Just point your camera at your food and let our advanced computer vision do the heavy lifting.</p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-primary">Start Free Trial</a>
                <a href="#features" class="btn" style="background: rgba(255,255,255,0.1); color: #fff; backdrop-filter: blur(10px);">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features">
        <div class="container">
            <div class="features-grid">
                <!-- Card 1 -->
                <div class="glass-card">
                    <span class="card-icon">📸</span>
                    <h3>Instant Vision API</h3>
                    <p>Our proprietary model identifies over 10,000 foods instantly from a single photo with 94% verified accuracy.</p>
                </div>
                <!-- Card 2 -->
                <div class="glass-card">
                    <span class="card-icon">🥑</span>
                    <h3>Macro Breakdown</h3>
                    <p>Get more than just calories. See detailed protein, fat, carb, and micronutrient splits automatically.</p>
                </div>
                <!-- Card 3 -->
                <div class="glass-card">
                    <span class="card-icon">🔒</span>
                    <h3>Private by Design</h3>
                    <p>Your food logs are yours alone. We process images on-device whenever possible and never sell your data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="logo" style="justify-content: center; margin-bottom: 1rem; opacity: 0.5;">
                <div class="logo-icon" style="width: 20px; height: 20px;"></div>
                CalorieAI
            </div>
            <p>© 2025 Marvels. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
