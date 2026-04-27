#!/usr/bin/env php
<?php
/**
 * Script para precargar TODOS los usuarios de Mantis en cache
 * Uso: php precargar_usuarios.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->boot();

$token   = env('MANTIS_TOKEN');
$base    = env('MANTIS_BASE_URL') . '/api/rest/issues?page_size=200&sort=id&direction=ASC&page=';
$vistos  = [];
$usuarios = [];
$page    = 1;
$total   = 0;

echo "Cargando usuarios de Mantis...\n";

do {
    $ch = curl_init($base . $page);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
    ]);
    $raw    = curl_exec($ch);
    $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) break;

    $issues = json_decode($raw, true)['issues'] ?? [];
    $total += count($issues);

    foreach ($issues as $issue) {
        foreach (['reporter', 'handler'] as $campo) {
            $u = $issue[$campo] ?? null;
            if ($u && !empty($u['email']) && !isset($vistos[$u['id']])) {
                $vistos[$u['id']] = true;
                $usuarios[] = [
                    'id'       => $u['id'],
                    'username' => $u['name'],
                    'nombre'   => !empty($u['real_name']) ? $u['real_name'] : $u['name'],
                    'email'    => $u['email'],
                ];
            }
        }
        foreach ($issue['notes'] ?? [] as $note) {
            $u = $note['reporter'] ?? null;
            if ($u && !empty($u['email']) && !isset($vistos[$u['id']])) {
                $vistos[$u['id']] = true;
                $usuarios[] = [
                    'id'       => $u['id'],
                    'username' => $u['name'],
                    'nombre'   => !empty($u['real_name']) ? $u['real_name'] : $u['name'],
                    'email'    => $u['email'],
                ];
            }
        }
    }

    echo "Página $page — issues: $total — usuarios únicos: " . count($usuarios) . "\n";
    $page++;

} while (count($issues) === 200);

usort($usuarios, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
$resultado = array_values($usuarios);

// Guardar en cache de Lumen
\Illuminate\Support\Facades\Cache::put('mantis_usuarios', $resultado, 86400); // 24h
\Illuminate\Support\Facades\Cache::put('mantis_usuarios_ts', time(), 86400);

echo "\n✅ Cache precargado con " . count($resultado) . " usuarios únicos\n";
