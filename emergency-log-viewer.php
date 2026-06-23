<?php
/**
 * Debug Log Manager Tool — Emergency Log Viewer
 *
 * Standalone, read-only debug.log viewer that keeps working even when
 * WordPress itself cannot boot (e.g. a fatal error white-screens the site).
 * It is served directly by the web server and deliberately does NOT bootstrap
 * WordPress, so a fatal in any plugin/theme cannot take it down with the site.
 *
 * Access is protected by HTTP Basic auth. Enable the feature and set the
 * credentials from the plugin's Settings screen; bookmark the URL shown there
 * BEFORE you need it.
 */

define('DLCT_EMERGENCY', true);

header('X-Robots-Tag: noindex, nofollow', true);
header('Referrer-Policy: no-referrer');

$dlctConfigFile = __DIR__ . '/../../dlct-emergency/config.php';

function dlct_emergency_unavailable()
{
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Not available</title><p>Not available.</p>';
    exit;
}

function dlct_emergency_challenge()
{
    header('WWW-Authenticate: Basic realm="Debug Log Emergency Viewer"');
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Authentication required</title><p>Authentication required.</p>';
    exit;
}

function dlct_emergency_tail($file, $lines)
{
    $handle = @fopen($file, 'rb');
    if (!$handle) {
        return '';
    }
    $buffer = 4096;
    fseek($handle, 0, SEEK_END);
    $pos = ftell($handle);
    $data = '';
    $count = 0;
    while ($pos > 0 && $count <= $lines) {
        $read = ($pos - $buffer) > 0 ? $buffer : $pos;
        $pos -= $read;
        fseek($handle, $pos);
        $data = fread($handle, $read) . $data;
        $count = substr_count($data, "\n");
    }
    fclose($handle);
    $rows = explode("\n", $data);
    $rows = array_slice($rows, -($lines + 1));
    return implode("\n", $rows);
}

if (!is_readable($dlctConfigFile)) {
    dlct_emergency_unavailable();
}

$dlctConfig = include $dlctConfigFile;
if (!is_array($dlctConfig) || empty($dlctConfig['enabled']) || empty($dlctConfig['auth_user']) || empty($dlctConfig['auth_hash'])) {
    dlct_emergency_unavailable();
}

$dlctUser = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
$dlctPass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

if ($dlctUser === '' && $dlctPass === '') {
    $authHeader = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (stripos($authHeader, 'Basic ') === 0) {
        $decoded = base64_decode(substr($authHeader, 6));
        if ($decoded !== false && strpos($decoded, ':') !== false) {
            list($dlctUser, $dlctPass) = explode(':', $decoded, 2);
        }
    }
}

$userOk = hash_equals((string) $dlctConfig['auth_user'], (string) $dlctUser);
$passOk = password_verify((string) $dlctPass, (string) $dlctConfig['auth_hash']);

if (!$userOk || !$passOk) {
    usleep(750000);
    dlct_emergency_challenge();
}

$logPath = isset($dlctConfig['log_path']) ? $dlctConfig['log_path'] : '';
if ($logPath === '' || !is_file($logPath) || !is_readable($logPath)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Debug log file not found or not readable.\n";
    exit;
}

if (isset($_GET['download'])) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="debug-log.txt"');
    header('Content-Length: ' . filesize($logPath));
    readfile($logPath);
    exit;
}

$lines = isset($_GET['lines']) ? (int) $_GET['lines'] : 500;
if ($lines < 1) {
    $lines = 500;
}
if ($lines > 5000) {
    $lines = 5000;
}

$tail = dlct_emergency_tail($logPath, $lines);
$size = filesize($logPath);
$mtime = filemtime($logPath);

$selfUrl = strtok($_SERVER['REQUEST_URI'], '?');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Emergency Debug Log Viewer</title>
<style>
  :root { color-scheme: dark; }
  body { margin: 0; background: #0d1117; color: #c9d1d9; font: 14px/1.5 -apple-system, Segoe UI, Roboto, sans-serif; }
  header { padding: 14px 20px; background: #161b22; border-bottom: 1px solid #30363d; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
  header h1 { font-size: 15px; margin: 0; font-weight: 600; }
  header .meta { color: #8b949e; font-size: 12px; }
  header .spacer { flex: 1; }
  header a, header button { color: #c9d1d9; background: #21262d; border: 1px solid #30363d; border-radius: 6px; padding: 6px 12px; font-size: 13px; text-decoration: none; cursor: pointer; }
  header a:hover, header button:hover { background: #30363d; }
  pre { margin: 0; padding: 16px 20px; white-space: pre-wrap; word-break: break-word; font: 12.5px/1.55 ui-monospace, SFMono-Regular, Menlo, monospace; }
</style>
</head>
<body>
<header>
  <h1>Emergency Debug Log Viewer</h1>
  <span class="meta"><?php echo htmlspecialchars(size_format_dlct($size)); ?> · last write <?php echo htmlspecialchars(gmdate('Y-m-d H:i:s', $mtime)); ?> UTC · last <?php echo (int) $lines; ?> lines</span>
  <span class="spacer"></span>
  <a href="<?php echo htmlspecialchars($selfUrl); ?>">Refresh</a>
  <a href="<?php echo htmlspecialchars($selfUrl); ?>?download=1">Download raw</a>
</header>
<pre><?php echo htmlspecialchars($tail); ?></pre>
</body>
</html>
<?php
function size_format_dlct($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $bytes = (float) $bytes;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
