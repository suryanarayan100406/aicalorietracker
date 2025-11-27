<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Calorie AI Tracker - Velvet & Marble</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&family=Raleway:wght@400;500&display=swap" rel="stylesheet" />

  <!-- AOS (Animate On Scroll) Library -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />

  <style>
    :root {
      --velvet: #222D52;
      --marble: #E8E4E0;
      --text: #111;
      --light-text: #fff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Raleway', sans-serif;
      background: linear-gradient(160deg, var(--marble), #f6f5f3);
      color: var(--text);
      overflow-x: hidden;
      scroll-behavior: smooth;
      min-height: 100vh;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1050px;
      margin: 0 auto;
      padding: 1rem 1.6rem;
      background: var(--velvet);
      color: var(--light-text);
      border-bottom-left-radius: 14px;
      border-bottom-right-radius: 14px;
      box-shadow: 0 8px 20px rgba(34, 45, 82, 0.3);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    header h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.4rem;
      letter-spacing: 0.3px;
    }

    nav a {
      color: var(--light-text);
      margin-left: 1.4rem;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    nav a:hover {
      color: var(--marble);
      text-shadow: 0 0 8px rgba(232, 228, 224, 0.6);
    }

    /* Hero Section */
    .hero {
      text-align: center;
      max-width: 900px;
      margin: 3rem auto 2rem;
      background: var(--velvet);
      color: var(--light-text);
      border-radius: 20px;
      padding: 4rem 2rem;
      box-shadow: 0 20px 50px rgba(34, 45, 82, 0.25);
    }

    .hero h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 2.6rem;
      margin-bottom: 1rem;
    }

    .hero p {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.85);
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .hero img {
      width: 100%;
      max-width: 380px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
      margin-top: 2rem;
      animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    /* Buttons */
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn {
      padding: 0.9rem 1.8rem;
      border-radius: 14px;
      text-decoration: none;
      font-weight: 700;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .btn.primary {
      background: var(--marble);
      color: var(--velvet);
      box-shadow: 0 8px 25px rgba(232, 228, 224, 0.3);
    }

    .btn.primary:hover {
      transform: translateY(-5px);
      box-shadow: 0 14px 40px rgba(232, 228, 224, 0.5);
    }

    .btn.secondary {
      background: transparent;
      border: 2px solid var(--marble);
      color: var(--marble);
    }

    .btn.secondary:hover {
      background: var(--marble);
      color: var(--velvet);
      transform: translateY(-5px);
    }

    /* Features Section */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2rem;
      max-width: 1100px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .feature {
      background: var(--marble);
      border-radius: 20px;
      padding: 2rem;
      color: var(--velvet);
      transition: 0.3s;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .feature:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 35px rgba(34, 45, 82, 0.25);
    }

    .feature h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.3rem;
      margin-bottom: 0.8rem;
      color: var(--velvet);
    }

    .feature p {
      color: #222;
      line-height: 1.6;
    }

    footer {
      text-align: center;
      padding: 1.8rem;
      background: var(--velvet);
      color: var(--light-text);
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
      font-size: 0.9rem;
      box-shadow: 0 -4px 20px rgba(34, 45, 82, 0.2);
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 0.5rem;
      }
      .hero {
        margin: 2rem 1rem;
        padding: 3rem 1.4rem;
      }
      .hero h1 {
        font-size: 2.2rem;
      }
    }
  </style>
</head>
<body>
  <header data-aos="fade-down">
    <h2>Calorie AI Tracker</h2>
    <nav>
      <a href="#">Home</a>
      <a href="login.php">Sign In</a>
      <a href="register.php">Register</a>
    </nav>
  </header>

  <section class="hero" data-aos="fade-up" data-aos-duration="1200">
    <h1>Snap. Analyze. Thrive.</h1>
    <p>Transform your food tracking with AI precision. Snap your meals, get instant insights, and make nutrition effortless.</p>
    <div class="btn-group" data-aos="zoom-in" data-aos-delay="400">
      <a href="login.php" class="btn primary">Get Started</a>
      <a href="register.php" class="btn secondary">Join Free</a>
    </div>
    <img src="/public/assets/2.png" alt="Healthy food illustration" data-aos="fade-up" data-aos-delay="700" />
  </section>

  <section class="features">
    <div class="feature" data-aos="fade-right">
      <h3>⚡ Instant AI Insights</h3>
      <p>Snap a photo of your meal and get an instant calorie breakdown powered by AI.</p>
    </div>
    <div class="feature" data-aos="fade-left">
      <h3>🍃 Smart Meal Suggestions</h3>
      <p>Get personalized recommendations that help you stay on track with your goals.</p>
    </div>
    <div class="feature" data-aos="fade-right">
      <h3>📈 Progress Visualization</h3>
      <p>Track nutrition and habits in a clean, minimal dashboard.</p>
    </div>
    <div class="feature" data-aos="fade-left">
      <h3>🔒 Secure & Private</h3>
      <p>Your data stays yours — protected, private, and never shared.</p>
    </div>
  </section>

  <footer data-aos="fade-up" data-aos-duration="1000">
    &copy; 2025 Calorie AI Tracker. Crafted with ♥ in velvet & marble.
  </footer>

  <!-- AOS JS -->
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({
      once: true, // Animates only once when scrolled into view
      duration: 1000, // Default animation duration
      easing: 'ease-in-out'
    });
  </script>
</body>
</html>