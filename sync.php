<?php
// Eenvoudige web-trigger om collecties te synchroniseren.
// Alleen uitvoeren op localhost of met wachtwoord.
$allowed_ips = ['127.0.0.1', '::1'];
$password = 'sync5bevo'; // Verander dit naar een eigen wachtwoord

$ip = $_SERVER['REMOTE_ADDR'];
$authorized = in_array($ip, $allowed_ips) || ($_POST['pw'] ?? '') === $password;

$lastSyncFile = __DIR__ . '/last_sync.json';
$lockFile = __DIR__ . '/sync.lock';
$logFile = __DIR__ . '/sync_log.txt';

$output = '';
$ran = false;
$locked = false;

// Check lock file (< 10 min old = sync is running)
if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 600) {
    $locked = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $authorized && !$locked) {
    $script = escapeshellarg(__DIR__ . '/download_collections.py');
    $python = 'C:\Users\seb_p\AppData\Local\Python\bin\python.exe';
    $output = shell_exec('"' . $python . '" ' . $script . ' 2>&1');
    $ran = true;

    // Update last_sync.json
    file_put_contents($lastSyncFile, json_encode([
        'last_sync' => date('c'),
        'status' => 'ok',
    ]));
}

// Read last sync status
$lastSync = null;
if (file_exists($lastSyncFile)) {
    $lastSync = json_decode(file_get_contents($lastSyncFile), true);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Collecties synchroniseren</title>
<style>
  body { font-family: sans-serif; max-width: 700px; margin: 60px auto; padding: 0 20px; }
  pre  { background: #f4f4f4; padding: 16px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
  button { padding: 10px 24px; font-size: 16px; cursor: pointer; }
  input[type=password] { padding: 8px; font-size: 16px; margin-right: 8px; }
  .ok  { color: green; }
  .err { color: red; }
  .status { padding: 12px 16px; border-radius: 6px; background: #f4f4f4; margin-bottom: 1.5rem; }
  .status .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
  .status .dot.ok { background: green; }
  .status .dot.error { background: red; }
  details { margin-top: 1.5rem; }
  summary { cursor: pointer; font-size: 14px; color: #555; }
</style>
</head>
<body>
<h1>Collecties synchroniseren</h1>

<?php if ($lastSync): ?>
  <div class="status">
    <span class="dot <?= $lastSync['status'] === 'ok' ? 'ok' : 'error' ?>"></span>
    Laatste sync: <?= date('d-m-Y H:i', strtotime($lastSync['last_sync'])) ?>
    — status: <strong><?= htmlspecialchars($lastSync['status']) ?></strong>
  </div>
<?php endif; ?>

<?php if ($locked): ?>
  <p class="err">Sync is al bezig. Probeer het later opnieuw.</p>
<?php elseif ($ran): ?>
  <p class="ok">Sync uitgevoerd.</p>
  <pre><?= htmlspecialchars($output) ?></pre>
  <p><a href="sync.php">Opnieuw</a></p>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$authorized): ?>
  <p class="err">Verkeerd wachtwoord.</p>
<?php endif; ?>

<?php if (!$ran && !$locked): ?>
<form method="post">
  <?php if (!in_array($ip, $allowed_ips)): ?>
    <label>Wachtwoord: <input type="password" name="pw" autofocus></label>
  <?php endif; ?>
  <button type="submit">Ververs collecties</button>
</form>
<?php endif; ?>

<?php if (file_exists($logFile)): ?>
<details>
  <summary>Sync log</summary>
  <pre><?= htmlspecialchars(implode("\n", array_slice(
      file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50
  ))) ?></pre>
</details>
<?php endif; ?>

</body>
</html>
