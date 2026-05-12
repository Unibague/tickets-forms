<?php
/**
 * Precarga usuarios del LDAP en Mantis via API REST.
 * Solo crea los que NO existen aún en Mantis.
 * Uso: php precargar_usuarios_ldap.php [--dry-run] [--limit=50]
 */

$dotenv = __DIR__ . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv) as $line) {
        $line = trim($line);
        if (!$line || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}

$MANTIS_URL   = rtrim($_ENV['MANTIS_BASE_URL'] ?? 'https://ticketsdev.unibague.edu.co/tickets', '/');
$MANTIS_TOKEN = $_ENV['MANTIS_TOKEN'] ?? '';
$LDAP_HOST    = $_ENV['LDAP_HOST']    ?? '';
$LDAP_PORT    = (int)($_ENV['LDAP_PORT'] ?? 389);
$LDAP_BIND_DN = $_ENV['LDAP_BIND_DN'] ?? '';
$LDAP_BIND_PW = $_ENV['LDAP_BIND_PASSWORD'] ?? '';
$LDAP_BASE_DN = $_ENV['LDAP_BASE_DN'] ?? '';
$LDAP_FILTER  = $_ENV['LDAP_FILTER']  ?? '(objectClass=person)';
$EMAIL_FIELD  = $_ENV['LDAP_EMAIL_FIELD'] ?? 'gacctmail';

$dryRun = in_array('--dry-run', $argv ?? []);
$limit  = 0;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = (int)substr($arg, 8);
    }
}

echo "=== Precarga de usuarios LDAP → Mantis ===\n";
echo "Mantis: $MANTIS_URL\n";
echo "LDAP:   $LDAP_HOST / $LDAP_BASE_DN\n";
echo $dryRun ? "[DRY-RUN: no se creará nada]\n" : "[MODO REAL: se crearán usuarios]\n";
echo $limit   ? "Límite: $limit usuarios\n" : "";
echo "\n";

// ── 1. Obtener usuarios existentes en Mantis via SOAP ──────────────────────
echo "Obteniendo usuarios existentes en Mantis...\n";
$mantisEmails    = [];
$mantisUsernames = [];

$SOAP_URL  = $_ENV['MANTIS_SOAP_URL']  ?? '';
$SOAP_USER = $_ENV['MANTIS_SOAP_USER'] ?? '';
$SOAP_PASS = $_ENV['MANTIS_SOAP_PASS'] ?? '';

$xml = '<?xml version="1.0" encoding="utf-8"?>'
     . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
     . '<soap:Body>'
     . '<mc_project_get_users xmlns="http://futureware.biz/mantisconnect">'
     . '<username>' . htmlspecialchars($SOAP_USER) . '</username>'
     . '<password>' . htmlspecialchars($SOAP_PASS) . '</password>'
     . '<project_id>0</project_id>'
     . '<access>10</access>'
     . '</mc_project_get_users>'
     . '</soap:Body>'
     . '</soap:Envelope>';

$ch = curl_init($SOAP_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $xml,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "http://futureware.biz/mantisconnect/mc_project_get_users"',
    ],
]);
$raw = curl_exec($ch);
curl_close($ch);

if ($raw) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    if ($doc->loadXML($raw)) {
        foreach (iterator_to_array($doc->getElementsByTagName('item')) as $item) {
            $email = $item->getElementsByTagName('email')->item(0)->nodeValue ?? '';
            $name  = $item->getElementsByTagName('name')->item(0)->nodeValue ?? '';
            if ($email) $mantisEmails[strtolower($email)]   = true;
            if ($name)  $mantisUsernames[strtolower($name)] = true;
        }
    }
}

echo "Usuarios en Mantis: " . count($mantisUsernames) . "\n\n";

// ── 2. Obtener usuarios del LDAP ─────────────────────────────────────────────
echo "Conectando al LDAP...\n";
$conn = @ldap_connect($LDAP_HOST, $LDAP_PORT);
if (!$conn) die("ERROR: No se pudo conectar al LDAP\n");
ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);
if (!@ldap_bind($conn, $LDAP_BIND_DN, $LDAP_BIND_PW)) die("ERROR: Bind LDAP fallido\n");

$attrs  = ['uid', 'cn', 'name', 'sn', $EMAIL_FIELD, 'gacctmail', 'mail1', 'mail2', 'estado'];
$search = @ldap_search($conn, $LDAP_BASE_DN, $LDAP_FILTER, $attrs, 0, 0, 30);
if (!$search) die("ERROR: Búsqueda LDAP fallida\n");

$entries = ldap_get_entries($conn, $search);
ldap_unbind($conn);
echo "Usuarios en LDAP: " . $entries['count'] . "\n\n";

// ── 3. Filtrar los que NO están en Mantis ────────────────────────────────────
$pendientes = [];
for ($i = 0; $i < $entries['count']; $i++) {
    $e   = $entries[$i];
    $uid = strtolower($e['uid'][0] ?? '');

    $email = '';
    foreach ([$EMAIL_FIELD, 'gacctmail', 'mail2', 'mail1'] as $f) {
        $val = $e[strtolower($f)][0] ?? '';
        if (!empty($val) && $val !== '0' && filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower($val);
            break;
        }
    }
    if (empty($email) || empty($uid)) continue;

    // Saltar si ya existe en Mantis
    if (isset($mantisEmails[$email]) || isset($mantisUsernames[$uid])) continue;

    // Saltar inactivos
    $estado = strtolower($e['estado'][0] ?? 'activo');
    if ($estado === 'inactivo') continue;

    $nombre = trim(
        $e['name'][0]
        ?? trim(($e['cn'][0] ?? '') . ' ' . ($e['sn'][0] ?? ''))
        ?: $uid
    );

    $pendientes[] = [
        'username'  => $uid,
        'real_name' => $nombre ?: $uid,
        'email'     => $email,
    ];

    if ($limit && count($pendientes) >= $limit) break;
}

echo "Usuarios a crear en Mantis: " . count($pendientes) . "\n\n";

if (empty($pendientes)) {
    echo "✅ Todos los usuarios del LDAP ya existen en Mantis.\n";
    exit(0);
}

// ── 4. Crear en Mantis ───────────────────────────────────────────────────────
$creados  = 0;
$errores  = 0;
$omitidos = 0;

foreach ($pendientes as $u) {
    if ($dryRun) {
        echo "[DRY-RUN] Crearía: {$u['username']} <{$u['email']}> ({$u['real_name']})\n";
        $creados++;
        continue;
    }

    $payload = json_encode([
        'username'     => $u['username'],
        'real_name'    => $u['real_name'],
        'email'        => $u['email'],
        'password'     => bin2hex(random_bytes(16)), // contraseña aleatoria, login es por LDAP
        'access_level' => ['name' => 'reporter'],
    ]);

    $resp = mantisPost("$MANTIS_URL/api/rest/users", $payload);

    if (isset($resp['user']['id'])) {
        echo "✅ Creado: {$u['username']} <{$u['email']}> (ID: {$resp['user']['id']})\n";
        $creados++;
    } elseif (isset($resp['message']) && preg_match('/already used|already exist|duplicate/i', $resp['message'])) {
        echo "⏭  Ya existe: {$u['username']}\n";
        $omitidos++;
    } else {
        $msg = $resp['message'] ?? json_encode($resp);
        echo "❌ Error: {$u['username']} — $msg\n";
        $errores++;
    }

    usleep(100000); // 100ms entre requests para no saturar Mantis
}

echo "\n=== Resumen ===\n";
echo "Creados:  $creados\n";
echo "Omitidos: $omitidos\n";
echo "Errores:  $errores\n";

// ── Helpers ──────────────────────────────────────────────────────────────────
function mantisGet(string $url): array {
    global $MANTIS_TOKEN;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ["Authorization: $MANTIS_TOKEN"],
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    return json_decode($raw, true) ?? [];
}

function mantisPost(string $url, string $payload): array {
    global $MANTIS_TOKEN;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            "Authorization: $MANTIS_TOKEN",
            'Content-Type: application/json',
        ],
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    return json_decode($raw, true) ?? [];
}
