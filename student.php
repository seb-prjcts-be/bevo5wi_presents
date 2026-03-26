<?php
// Configuration
$title = "5BEVOwi Presents";
$baseDir = "Data/Leerlingen";

// Get parameters
$year = isset($_GET['year']) ? $_GET['year'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$student = isset($_GET['student']) ? $_GET['student'] : '';

// Security check - prevent directory traversal
if (empty($year) || empty($class) || empty($student) ||
    strpos($year, '..') !== false || strpos($class, '..') !== false || strpos($student, '..') !== false) {
    header('Location: index.php');
    exit;
}

$studentPath = "$baseDir/$year/$class/$student";
$studentName = str_replace('_', ' ', $student);

// Check if student directory exists
if (!is_dir($studentPath)) {
    header('Location: index.php');
    exit;
}

// Function to get p5 projects
function getP5Projects($studentPath) {
    $p5Path = "$studentPath/p5";
    $projects = array();
    
    if (is_dir($p5Path)) {
        $items = scandir($p5Path);
        foreach ($items as $item) {
            if ($item != "." && $item != ".." && is_dir("$p5Path/$item")) {
                $projects[] = $item;
            }
        }
    }
    
    return $projects;
}

// Function to get media files
function getMediaFiles($studentPath) {
    $mediaPath = "$studentPath/media";
    $files = array();
    
    if (is_dir($mediaPath)) {
        $items = scandir($mediaPath);
        foreach ($items as $item) {
            if ($item != "." && $item != ".." && is_file("$mediaPath/$item")) {
                $files[] = $item;
            }
        }
    }
    
    return $files;
}

// Get projects, media and collection URL
$p5Projects = getP5Projects($studentPath);
$mediaFiles = getMediaFiles($studentPath);

$collectionUrl = null;
$collectionFile = "$studentPath/collection.json";
if (is_file($collectionFile)) {
    $json = json_decode(file_get_contents($collectionFile), true);
    if (isset($json['collection_url'])) {
        $collectionUrl = $json['collection_url'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "$studentName - $title"; ?></title>
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
            <div class="back-link">
                <a href="index.php" title="Back to Overview">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>
            <div class="header-content">
                <h1 class="page-title"><?php echo htmlspecialchars(strtoupper($studentName)); ?></h1>
            </div>
        </header>

        <main>
            <?php if ($collectionUrl): ?>
            <section class="collection-section card">
                <h2 class="card__title">P5.JS COLLECTIE</h2>
                <a href="<?php echo htmlspecialchars($collectionUrl); ?>" class="button" target="_blank" rel="noopener">Bekijk collectie op p5js.org</a>
            </section>
            <?php endif; ?>

            <section class="projects-section card">
                <h2 class="card__title">P5.JS PROJECTS</h2>
                <?php if (empty($p5Projects)): ?>
                    <p class="empty-message">No p5.js projects found.</p>
                <?php else: ?>
                    <div class="project-grid">
                        <?php 
                        $i = 0;
                        foreach ($p5Projects as $project): 
                            $colorIndex = ($i % 15) + 1; // Cycle through the 15 colors
                            $i++;
                        ?>
                            <div class="card project-card" style="border-left: 4px solid var(--color-palette-<?php echo $colorIndex; ?>);">
                                <h3 class="text-left"><a href="<?php echo "$studentPath/p5/$project"; ?>" class="button"><?php echo htmlspecialchars(strtoupper($project)); ?></a></h3>
                               
                                <!-- <h3 class="text-left"><?php echo htmlspecialchars(strtoupper($project)); ?></h3>
                                <a href="<?php echo "$studentPath/p5/$project"; ?>" class="button">VIEW PROJECT</a> -->

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="media-section card">
                <!-- <h2 class="card__title">MEDIA FILES</h2> -->
                <?php if (empty($mediaFiles)): ?>
                    <!-- <p class="empty-message">No media files found.</p> -->
                <?php else: ?>
                    <div class="media-grid">
                         <h2 class="card__title">MEDIA FILES</h2>
                        <?php 
                        $i = 0;
                        foreach ($mediaFiles as $file): 
                            $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
                            $isVideo = in_array($fileExt, ['mp4', 'webm', 'ogg']);
                            $isAudio = in_array($fileExt, ['mp3', 'wav', 'ogg']);
                            $colorIndex = ($i % 15) + 1; // Cycle through the 15 colors
                            $i++;
                        ?>
                            <div class="card media-card" style="border-bottom: 4px solid var(--color-palette-<?php echo $colorIndex; ?>);">
                                <?php if ($isImage): ?>
                                    <div class="media-preview">
                                        <img src="<?php echo "$studentPath/media/$file"; ?>" alt="<?php echo htmlspecialchars($file); ?>">
                                    </div>
                                <?php elseif ($isVideo): ?>
                                    <div class="media-preview">
                                        <video controls>
                                            <source src="<?php echo "$studentPath/media/$file"; ?>" type="video/<?php echo $fileExt; ?>">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php elseif ($isAudio): ?>
                                    <div class="media-preview">
                                        <audio controls>
                                            <source src="<?php echo "$studentPath/media/$file"; ?>" type="audio/<?php echo $fileExt; ?>">
                                            Your browser does not support the audio tag.
                                        </audio>
                                    </div>
                                <?php else: ?>
                                    <div class="media-preview file-icon">
                                        <span><?php echo strtoupper($fileExt); ?></span>
                                    </div>
                                <?php endif; ?>
                                <h3 class="text-center"><?php echo htmlspecialchars(strtoupper($file)); ?></h3>
                                <a href="<?php echo "$studentPath/media/$file"; ?>" class="button" download>DOWNLOAD</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <footer class="text-center">
            <p class="text-center">&copy; <?php echo date('Y'); ?> 5BEVO PRESENTS - ALL RIGHTS RESERVED</p>
        </footer>
    </div>
</body>
</html>
