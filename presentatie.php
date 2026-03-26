<?php
$baseDir = "Data/Leerlingen";
$presentYear = "2025-2026";

$hiddenStudents = ['Astor_Meire'];
$sketches = array();

$yearPath = "$baseDir/$presentYear";
if (is_dir($yearPath)) {
    foreach (scandir($yearPath) as $class) {
        if ($class === '.' || $class === '..') continue;
        $classPath = "$yearPath/$class";
        if (!is_dir($classPath)) continue;

        foreach (scandir($classPath) as $student) {
            if ($student === '.' || $student === '..') continue;
            if (in_array($student, $hiddenStudents)) continue;
            $studentPath = "$classPath/$student";
            if (!is_dir($studentPath)) continue;

            $p5Path = "$studentPath/p5";
            if (!is_dir($p5Path)) continue;

            foreach (scandir($p5Path) as $project) {
                if ($project === '.' || $project === '..') continue;
                $projectPath = "$p5Path/$project";
                if (!is_dir($projectPath)) continue;
                if (!is_file("$projectPath/index.html")) continue;

                $sketches[] = array(
                    'url'     => "$studentPath/p5/$project/index.html",
                    'name'    => str_replace('_', ' ', $student),
                    'project' => $project,
                    'class'   => strtoupper($class),
                );
            }
        }
    }
}

shuffle($sketches);
$sketchesJson = json_encode($sketches);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentatie – 5BEVOwi Presents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #fff;
            color: var(--color-text, #111);
            font-family: var(--font-primary, 'Inter', sans-serif);
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: stretch;
        }

        #slide {
            flex: 1;
            position: relative;
            min-width: 0;
        }

        #slide iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Code-paneel — decoratief, naast de schets */
        #code-panel {
            width: 380px;
            flex-shrink: 0;
            align-self: center;
            max-height: calc(100vh - 2.4rem);
            margin: 1.2rem 1.2rem 1.2rem 0;
            background: rgba(246, 248, 250, 0.96);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            pointer-events: none;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        }

        #code-panel pre {
            margin: 0;
            flex: 1;
            padding: 1.2rem 1.2rem;
            font-size: 11px;
            line-height: 1.55;
            background: transparent;
            overflow: hidden;
            scrollbar-width: none;
        }

        #code-panel pre::-webkit-scrollbar { display: none; }

        #code-panel code {
            font-family: 'Fira Mono', 'Consolas', monospace;
            white-space: pre;
        }

        #code-panel-title { display: none; }


        /* Overlay onderaan met naam */
        #nameplate {
            position: fixed;
            bottom: 0;
            left: 0;
            right: calc(380px + 1.2rem);
            padding: 1.2rem 2rem;
            background: linear-gradient(transparent, rgba(245,245,245,0.9));
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            pointer-events: none;
            z-index: 10;
        }

        #nameplate .maker {
            font-size: 2.5rem;
            font-weight: 200;
            letter-spacing: 3px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        #nameplate .meta {
            font-size: 0.85rem;
            opacity: 0.6;
            text-align: right;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Voortgangsbalk */
        #progress-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 3px;
            background: var(--color-text, #111);
            z-index: 20;
            width: 0%;
            transition: none;
        }

        /* Logo bovenaan links — altijd zichtbaar als home-knop */
        #logo-home {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 30;
        }

        #logo-home img {
            transition: filter 0.2s;
            filter: none;
        }

        #logo-home:hover img {
            filter: opacity(0.4) drop-shadow(0 0 0 #888);
        }

        #logo-subtitle {
            margin-top: 0.4rem;
            font-size: 14px;
            color: #111;
            letter-spacing: 0.5px;
            font-weight: 300;
        }

        /* Navigatie-knoppen */
        #controls {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 30;
            opacity: 0;
            transition: opacity 0.3s;
        }

        body:hover #controls { opacity: 1; }

        #controls button, #controls a {
            background: rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.2);
            color: var(--color-text, #111);
            padding: 6px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            letter-spacing: 1px;
            text-transform: uppercase;
            backdrop-filter: blur(4px);
            transition: background 0.2s;
            font-family: inherit;
        }

        #controls button:hover, #controls a:hover {
            background: rgba(0,0,0,0.18);
        }

        /* Slide-teller */
        #counter {
            position: fixed;
            top: 1rem;
            left: 1rem;
            font-size: 0.8rem;
            opacity: 0;
            letter-spacing: 2px;
            z-index: 30;
            text-transform: uppercase;
            transition: opacity 0.3s;
        }

        body:hover #counter { opacity: 0.5; }

        /* Fade-overlay bij wisselen */
        #fade {
            position: fixed;
            top: 0; bottom: 0; left: 0;
            right: calc(380px + 1.2rem);
            background: #fff;
            z-index: 5;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }
        #fade.active { opacity: 1; }

        #pause-icon {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            opacity: 0;
            pointer-events: none;
            z-index: 40;
            transition: opacity 0.3s;
        }
        #pause-icon.show { opacity: 0.6; }
    </style>
</head>
<body>

<div id="fade"></div>

<div id="slide">
    <iframe id="sketch-frame" src="" sandbox="allow-scripts allow-same-origin" allowfullscreen></iframe>
</div>

<div id="code-panel">
    <div id="code-panel-title">sketch.js</div>
    <pre><code id="code-content" class="language-javascript"></code></pre>
</div>

<div id="nameplate">
    <div class="maker" id="maker-name"></div>
    <div class="meta">
        <div id="project-name"></div>
        <div id="class-name"></div>
    </div>
</div>

<div id="progress-bar"></div>

<div id="counter"></div>

<div id="pause-icon">⏸</div>

<a id="logo-home" href="index.php" title="Terug naar home">
    <img src="huisstijl/logo/Logo-groot-zwart.jpg" alt="5BEVO Logo" class="logo-img">
    <br><br><p id="logo-subtitle">Projecten uit de lessen Digitaal Labo - Code, van 5BEVOwi.</p>
</a>

<div id="controls">
    <button onclick="prevSlide()">&#9664; Vorige</button>
    <button onclick="togglePause()" id="pause-btn">Pauze</button>
    <button onclick="nextSlide()">Volgende &#9654;</button>
<a href="index.php">&#10005; Sluiten</a>
</div>

<script>
const sketches = <?php echo $sketchesJson; ?>;
const DURATION = 5000; // milliseconden per schets

let current = 0;
let paused = false;
let timer = null;
let progressTimer = null;
let progressStart = null;
let elapsed = 0;

const frame      = document.getElementById('sketch-frame');
const codeContent = document.getElementById('code-content');

async function loadCode(slideUrl) {
    const sketchUrl = slideUrl.replace('index.html', 'sketch.js');
    try {
        const res = await fetch(sketchUrl);
        const text = await res.text();
        codeContent.textContent = text;
    } catch(e) {
        codeContent.textContent = '// sketch.js niet gevonden';
    }
}

// Centreer de canvas binnen het iframe na laden
frame.addEventListener('load', () => {
    try {
        const doc = frame.contentDocument;
        if (!doc) return;
        const existing = doc.getElementById('centering-style');
        if (existing) existing.remove();
        const s = doc.createElement('style');
        s.id = 'centering-style';
        s.textContent = `
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                overflow: hidden !important;
            }
            canvas { display: block !important; }
        `;
        if (doc.head) doc.head.appendChild(s);
        else if (doc.body) doc.body.insertBefore(s, doc.body.firstChild);
    } catch(e) {}
});
const makerEl    = document.getElementById('maker-name');
const projectEl  = document.getElementById('project-name');
const classEl    = document.getElementById('class-name');
const bar        = document.getElementById('progress-bar');
const counterEl  = document.getElementById('counter');
const fade       = document.getElementById('fade');
const pauseIcon  = document.getElementById('pause-icon');
const pauseBtn   = document.getElementById('pause-btn');

function showSlide(index) {
    if (!sketches.length) return;
    const s = sketches[index];

    fade.classList.add('active');

    setTimeout(() => {
        frame.src = s.url;
        makerEl.textContent  = s.name.toUpperCase();
        projectEl.textContent = s.project;
        classEl.textContent  = '5BEVOwi/' + s.class.toLowerCase();
        counterEl.textContent = (index + 1) + ' / ' + sketches.length;
        loadCode(s.url);
        fade.classList.remove('active');
    }, 400);
}

function startProgress() {
    clearInterval(progressTimer);
    progressStart = performance.now() - elapsed;
    bar.style.transition = 'none';
    bar.style.width = (elapsed / DURATION * 100) + '%';

    requestAnimationFrame(() => {
        progressTimer = setInterval(() => {
            if (paused) return;
            const pct = Math.min((performance.now() - progressStart) / DURATION * 100, 100);
            bar.style.width = pct + '%';
        }, 30);
    });
}

function scheduleNext() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        if (!paused) {
            elapsed = 0;
            nextSlide();
        }
    }, DURATION - elapsed);
}

function nextSlide() {
    elapsed = 0;
    current = (current + 1) % sketches.length;
    showSlide(current);
    startProgress();
    scheduleNext();
}

function prevSlide() {
    elapsed = 0;
    current = (current - 1 + sketches.length) % sketches.length;
    showSlide(current);
    startProgress();
    if (!paused) scheduleNext();
}

function togglePause() {
    paused = !paused;
    if (paused) {
        elapsed = performance.now() - progressStart;
        clearTimeout(timer);
        pauseBtn.textContent = 'Hervat';
        pauseIcon.classList.add('show');
        setTimeout(() => pauseIcon.classList.remove('show'), 800);
    } else {
        pauseBtn.textContent = 'Pauze';
        startProgress();
        scheduleNext();
    }
}

// Toetsenbord
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight' || e.key === ' ') { elapsed = 0; nextSlide(); }
    if (e.key === 'ArrowLeft') { prevSlide(); }
    if (e.key === 'p' || e.key === 'P' || e.key === 'Escape') { togglePause(); }
});

// Start
if (sketches.length) {
    showSlide(current);
    startProgress();
    scheduleNext();
}
</script>
</body>
</html>
