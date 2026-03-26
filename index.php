<?php
// Configuration
$title = "5BEVO Presents";
$baseDir = "Data/Leerlingen";

// Function to get subdirectories
function getSubDirs($dir) {
    $dirs = array();
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item != "." && $item != ".." && is_dir("$dir/$item")) {
                $dirs[] = $item;
            }
        }
    }
    return $dirs;
}

// Get all years (sorted descending = meest recent eerst)
$years = getSubDirs($baseDir);
rsort($years);

// Verberg jaar 2024-2025 uit de presentatie (data blijft bewaard)
$years = array_filter($years, fn($y) => $y !== '2024-2025');

// Leerlingen die niet getoond worden (verandering van richting, data blijft bewaard)
$hiddenStudents = ['Astor_Meire'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <script src="js/dynamic-effects.js"></script>
</head>
<body>
    <div id="dynamic-header-container" style="position: relative;">
        <div id="dynamic-header"></div>
        <?php
        // Randomly select one of the two logo files
        $logoFiles = array('huisstijl/logo/Logo-groot-wit.jpg', 'huisstijl/logo/Logo-groot-zwart.jpg');
        $randomLogo = $logoFiles[array_rand($logoFiles)];
        ?>
        <div style="position: absolute; top: 10px; left: 20px; z-index: 100;">
            <a href="index.php" title="Home">
                <img src="<?php echo $randomLogo; ?>" alt="5BEVO Logo" class="logo-img">
            </a>
        </div>
    </div>
    <div class="container">
        <header>
            <div class="header-content">
                <h1 class="page-title">Creative Coding met p5.js.</h1>
            </div>
        </header>

        <main>
            <div style="margin-bottom: 2rem;">
                <a href="presentatie.php" class="button" style="font-size: 1.1rem; padding: 12px 28px;">&#9654; Presentatie 2025-2026</a>
            </div>

            <?php foreach ($years as $year): ?>
                <section class="year-section">
                    <h2 class="year-title"><?php echo htmlspecialchars($year); ?></h2>
                    <?php
                    $classes = getSubDirs("$baseDir/$year");
                    foreach ($classes as $class):
                    ?>
                    <section class="class-section">
                        <h3 class="card__title">5BEVOwi/<?php echo htmlspecialchars($class); ?></h3>
                        <div class="student-grid">
                            <?php
                            $students = getSubDirs("$baseDir/$year/$class");
                            $i = 0;
                            foreach ($students as $student):
                                if (in_array($student, $hiddenStudents)) continue;
                                $studentName = str_replace('_', ' ', $student);
                                $colorIndex = ($i % 15) + 1;
                                $i++;
                            ?>
                                <div class="card student-card" style="border-top: 4px solid var(--color-palette-<?php echo $colorIndex; ?>);">
                                    <h3 class="student-name"><?php echo htmlspecialchars(strtoupper($studentName)); ?></h3>
                                    <a href="student.php?year=<?php echo urlencode($year); ?>&class=<?php echo urlencode($class); ?>&student=<?php echo urlencode($student); ?>" class="button">Projecten</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        </main>

        <footer class="text-center">
            <p class="text-center">&copy; <?php echo date('Y'); ?>5BEVOwi Presents - ALL RIGHTS RESERVED</p>
        </footer>
    </div>
</body>
</html>
