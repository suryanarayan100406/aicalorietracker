<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/controllers/MealController.php'; // Handles form POST & upload
// MealController handles upload on POST and redirects to result or shows $error.
// We'll assume $error is populated by the controller if something goes wrong.
if (!isset($error)) {
    $error = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Meal — Calorie AI</title>
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
        
        /* --- Upload Form Styles --- */
        .upload-area {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .upload-drop {
            flex: 1 1 400px;
            min-height: 250px;
            border-radius: 14px;
            border: 2px dashed rgba(0, 245, 160, 0.3);
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .upload-drop.hover {
            border-color: var(--accent-glow);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 245, 160, 0.1);
        }
        .upload-drop .hint { text-align:center; color: var(--text-secondary); }
        .upload-drop .icon {
            width: 60px; height: 60px; border-radius: 50%;
            background: rgba(0, 245, 160, 0.1);
            color: var(--accent-glow);
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 0 20px rgba(0, 245, 160, 0.2);
        }
        .upload-drop .icon svg { width: 32px; height: 32px; }
        .upload-drop .hint-title { font-weight: 700; color: var(--text-primary); font-size: 1.1rem; }
        .upload-drop .hint-meta { font-size: 0.9rem; }
        
        .upload-drop input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .preview {
            flex: 1 1 260px;
            max-width: 300px;
            min-height: 250px;
            border-radius: 14px;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            padding: 1rem;
            align-items: center;
            justify-content: center;
        }
        .preview img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            display: block;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .preview .file-name {
            font-size: 0.95rem;
            color: var(--text-primary);
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
            font-weight: 600;
        }
        .preview .meta { font-size: 0.85rem; color: var(--text-secondary); }

        .form-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        
        label.checkbox {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            font-size: 0.9rem;
        }
        label.checkbox:hover {
            color: var(--text-primary);
        }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-glow);
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
        .btn.primary {
            background: var(--accent-gradient);
            color: #000;
            box-shadow: 0 0 25px rgba(0, 245, 160, 0.3);
        }
        .btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 245, 160, 0.5);
        }
        .btn.ghost {
            background: rgba(255,255,255,0.1);
            color: #fff;
            backdrop-filter: blur(10px);
        }
        .btn.ghost:hover {
            background: rgba(255,255,255,0.15);
        }
        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        /* --- Side Widgets --- */
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
            padding-left: 0; /* Remove default padding */
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
            .upload-area {
                flex-direction: column;
            }
            .preview {
                max-width: 100%;
                width: 100%;
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
                    <a href="upload.php" class="active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><line x1="16" y1="5" x2="22" y2="5"/><line x1="19" y1="2" x2="19" y2="8"/><path d="M3 17.5v-11a2 2 0 0 1 2-2h11.5c.9 0 1.7.5 2.1 1.2l3 5c.4.8.4 1.8 0 2.6l-3 5c-.4.7-1.2 1.2-2.1 1.2H5a2 2 0 0 1-2-2z"/><circle cx="9" cy="12" r="2"/></svg>
                        <span class="link-text">Upload Meal</span>
                    </a>
                    <a href="diary.php">
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
                    <h1 id="upload-heading">Upload Meal</h1>
                    <p class="lead">Snap a photo to get an instant AI analysis.</p>
                </div>
            </div>

            <!-- Main Widget Grid -->
            <div class="widget-grid">
                
                <!-- Left Column: Upload Form -->
                <section class="glass-card" aria-labelledby="upload-heading">
                    
                    <form method="POST" enctype="multipart/form-data" novalidate id="upload-form">
                        <div class="upload-area">
                            <!-- Dropzone + file input -->
                            <label class="upload-drop" id="dropzone" for="photo" tabindex="0" aria-describedby="upload-hint">
                                <div class="hint" id="upload-hint" aria-hidden="true">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </div>
                                    <div class="hint-title">Click or drag file to upload</div>
                                    <div class="hint-meta">JPEG, PNG — max 10MB</div>
                                </div>
                                <input type="file" name="photo" id="photo" accept="image/*" required aria-required="true"/>
                            </label>

                            <!-- Preview column -->
                            <div class="preview" id="preview" aria-live="polite" aria-atomic="true">
                                <div style="font-weight:700;color:var(--text-secondary);">Preview</div>
                                <img src="https://placehold.co/400x300/111111/a1a1aa?text=Your+Meal" alt="Preview image" id="preview-img" />
                                <div class="file-name" id="preview-name">No file selected</div>
                                <div class="meta" id="preview-meta">Image will appear here</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="checkbox">
                                <input type="checkbox" name="is_leftover" id="is_leftover">
                                This is a leftover
                            </label>
                            <div style="flex:1;"></div>
                            <button type="button" id="clear-btn" class="btn btn-ghost" title="Clear selection" aria-label="Clear selection">Clear</button>
                            <button type="submit" class="btn primary" id="analyze-btn" aria-label="Analyze photo" disabled>Analyze</button>
                        </div>

                        <?php if (isset($error) && !empty($error)): ?>
                            <p class="err" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                        <?php endif; ?>
                    </form>
                </section>
                
                <!-- Right Column: Widgets -->
                <aside class="side">
                    <div class="widget-card glass-card" role="region" aria-labelledby="tips">
                        <h3 id="tips">Upload Tips</h3>
                        <ul>
                            <li>Place the meal on a plain surface for best detection.</li>
                            <li>Include a visible portion of the plate for scale.</li>
                            <li>Good, natural lighting improves accuracy.</li>
                            <li>Avoid blurry or dark photos.</li>
                        </ul>
                    </div>

                    <div class="widget-card glass-card" role="region" aria-labelledby="shortcuts">
                        <h3 id="shortcuts">Shortcuts</h3>
                        <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:.4rem;">
                            <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
                            <a href="diary.php" class="btn btn-ghost">Food Diary</a>
                            <a href="profile.php" class="btn btn-ghost">Profile & Goals</a>
                        </div>
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

            // --- Upload Form Logic (from original file) ---
            const drop = document.getElementById('dropzone');
            const fileInput = document.getElementById('photo');
            const previewImg = document.getElementById('preview-img');
            const previewName = document.getElementById('preview-name');
            const previewMeta = document.getElementById('preview-meta');
            const analyzeBtn = document.getElementById('analyze-btn');
            const clearBtn = document.getElementById('clear-btn');
            const form = document.getElementById('upload-form');
            const defaultPreviewImg = 'https://placehold.co/400x300/111111/a1a1aa?text=Your+Meal';

            if (!form) return; // Exit if form elements aren't found

            function humanFileSize(bytes) {
                const thresh = 1024;
                if (Math.abs(bytes) < thresh) return bytes + ' B';
                const units = ['KB','MB','GB','TB'];
                let u = -1;
                do {
                    bytes /= thresh;
                    ++u;
                } while(Math.abs(bytes) >= thresh && u < units.length - 1);
                return bytes.toFixed(1)+' '+units[u];
            }

            function handleFile(file) {
                if (!file) return resetPreview();
                if (!file.type.startsWith('image/')) {
                    previewName.textContent = 'Invalid file type';
                    previewMeta.textContent = 'Please select a JPEG or PNG.';
                    analyzeBtn.disabled = true;
                    return;
                }
                // size guard (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    previewName.textContent = 'File too large (max 10MB)';
                    previewMeta.textContent = humanFileSize(file.size);
                    analyzeBtn.disabled = true;
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e){
                    previewImg.src = e.target.result;
                    previewName.textContent = file.name;
                    previewMeta.textContent = humanFileSize(file.size);
                    analyzeBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            }

            function resetPreview(){
                previewImg.src = defaultPreviewImg;
                previewName.textContent = 'No file selected';
                previewMeta.textContent = 'Image will appear here';
                analyzeBtn.disabled = true;
                fileInput.value = ''; // Clear the file input
            }

            // Focus / keyboard support for dropzone
            drop.addEventListener('click', (e) => {
                // Prevent click on label from firing twice
                if (e.target.id !== 'photo') {
                    fileInput.click();
                }
            });
            drop.addEventListener('keydown', (e)=>{
                if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); fileInput.click(); }
            });

            // handle selection via file input
            fileInput.addEventListener('change', (e)=>{
                const f = e.target.files && e.target.files[0];
                handleFile(f);
            });

            // drag & drop support
            ['dragenter','dragover'].forEach(evt=>{
                drop.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); drop.classList.add('hover'); });
            });
            ['dragleave','drop'].forEach(evt=>{
                drop.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); drop.classList.remove('hover'); });
            });
            drop.addEventListener('drop', (e)=>{
                const dt = e.dataTransfer;
                const f = dt && dt.files && dt.files[0];
                if (f) {
                    try {
                        fileInput.files = dt.files;
                    } catch (err) {
                        console.warn('Could not set file input programmatically:', err);
                    }
                    handleFile(f);
                }
            });

            // clear button
            clearBtn.addEventListener('click', (e)=>{
                e.preventDefault();
                resetPreview();
            });

            // on form submit, ensure a file exists
            form.addEventListener('submit', (e)=>{
                if (!fileInput.files || !fileInput.files[0]) {
                    e.preventDefault();
                    previewName.textContent = 'Please select an image first';
                    previewMeta.textContent = '';
                    analyzeBtn.disabled = true;
                    return;
                } else {
                    // good: allow submit, but prevent double-submit while uploading
                    analyzeBtn.disabled = true;
                    analyzeBtn.textContent = "Analyzing...";
                }
            });

            // initialize
            resetPreview();
        })();
    </script>

</body>
</html>
