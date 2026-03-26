<?php
// Eenvoudige web-trigger om collecties te synchroniseren.
// Alleen uitvoeren op localhost of met wachtwoord.
$allowed_ips = ['127.0.0.1', '::1'];
$password = 'sync5bevo'; // Verander dit naar een eigen wachtwoord

$ip = $_SERVER['REMOTE_ADDR'];
$authorized = in_array($ip, $allowed_ips) || ($_POST['pw'] ?? '') === $password;

$output = '';
$ran = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $authorized) {
    $script = escapeshellarg(__DIR__ . '/download_collections.py');
    $python = 'C:\Users\seb_p\AppData\Local\Python\bin\python.exe';
    $output = shell_exec('"' . $python . '" ' . $script . ' 2>&1');
    $ran = true;
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
</style>
</head>
<body>
<h1>Collecties synchroniseren</h1>

<?php if ($ran): ?>
  <p class="ok">Sync uitgevoerd.</p>
  <pre><?= htmlspecialchars($output) ?></pre>
  <p><a href="sync.php">Opnieuw</a></p>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$authorized): ?>
  <p class="err">Verkeerd wachtwoord.</p>
<?php endif; ?>

<?php if (!$ran): ?>
<form method="post">
  <?php if (!in_array($ip, $allowed_ips)): ?>
    <label>Wachtwoord: <input type="password" name="pw" autofocus></label>
  <?php endif; ?>
  <button type="submit">Ververs collecties</button>
</form>
<?php endif; ?>

</body>
</html>
