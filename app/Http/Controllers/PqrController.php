<?php

namespace App\Http\Controllers;

use App\Mail\PqrCopiaNotificacion;
use App\Mail\PqrLiderNotificacion;
use App\Mail\PqrNecesitaDatosNotificacion;
use App\Mail\PqrUsuarioNotificacion;
use App\Services\MantisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PqrController extends Controller
{
    private MantisService $mantisService;

    public function __construct(MantisService $mantisService)
    {
        $this->mantisService = $mantisService;
    }

    // GET /pqrs/usuarios — usuarios de Mantis (SOAP) + LDAP combinados
    public function usuarios()
    {
        $mantisUsuarios = $this->obtenerUsuariosMantis();
        $ldapUsuarios   = $this->obtenerUsuariosLdap();

        // Combinar: LDAP es la fuente principal, Mantis enriquece con el id interno
        $vistos   = [];
        $usuarios = [];

        // Indexar Mantis por email para lookup rápido
        $mantisIndex = [];
        foreach ($mantisUsuarios as $u) {
            if (!empty($u['email'])) {
                $mantisIndex[strtolower($u['email'])] = $u;
            }
        }

        // Primero los del LDAP
        foreach ($ldapUsuarios as $u) {
            $email = strtolower($u['email'] ?? '');
            if (empty($email) || isset($vistos[$email])) continue;
            $vistos[$email] = true;
            $mantis = $mantisIndex[$email] ?? [];
            $usuarios[] = [
                'id'       => $mantis['id'] ?? 0,
                'username' => $u['username'],
                'nombre'   => $u['nombre'],
                'email'    => $email,
                'fuente'   => 'ldap',
            ];
        }

        // Agregar los de Mantis que no estén en LDAP
        foreach ($mantisUsuarios as $u) {
            $email = strtolower($u['email'] ?? '');
            if (empty($email) || isset($vistos[$email])) continue;
            $vistos[$email] = true;
            $usuarios[] = [
                'id'       => $u['id'],
                'username' => $u['username'],
                'nombre'   => $u['nombre'],
                'email'    => $email,
                'fuente'   => 'mantis',
            ];
        }

        usort($usuarios, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
        return response()->json(array_values($usuarios));
    }

    private function obtenerUsuariosMantis(): array
    {
        $soapUrl = env('MANTIS_SOAP_URL');
        $user    = env('MANTIS_SOAP_USER');
        $pass    = env('MANTIS_SOAP_PASS');
        $vistos  = [];
        $result  = [];

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
             . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
             . '<soap:Body>'
             . '<mc_project_get_users xmlns="http://futureware.biz/mantisconnect">'
             . '<username>' . htmlspecialchars($user) . '</username>'
             . '<password>' . htmlspecialchars($pass) . '</password>'
             . '<project_id>0</project_id>'
             . '<access>10</access>'
             . '</mc_project_get_users>'
             . '</soap:Body>'
             . '</soap:Envelope>';

        $ch = curl_init($soapUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://futureware.biz/mantisconnect/mc_project_get_users"',
            ],
        ]);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$raw) return [];

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        if (!$doc->loadXML($raw)) return [];

        foreach (iterator_to_array($doc->getElementsByTagName('item')) as $item) {
            $email = $item->getElementsByTagName('email')->item(0)->nodeValue ?? '';
            $name  = $item->getElementsByTagName('name')->item(0)->nodeValue ?? '';
            $rname = $item->getElementsByTagName('real_name')->item(0)->nodeValue ?? '';
            if (empty($email) || empty($name) || isset($vistos[$email])) continue;
            $vistos[$email] = true;
            $result[] = [
                'id'       => (int)($item->getElementsByTagName('id')->item(0)->nodeValue ?? 0),
                'username' => $name,
                'nombre'   => !empty($rname) ? $rname : $name,
                'email'    => $email,
            ];
        }

        return $result;
    }

    private function obtenerUsuariosLdap(bool $conCargo = false): array
    {
        $host   = env('LDAP_HOST',          'ldap://plataforma1.unibague.edu.co');
        $port   = (int) env('LDAP_PORT',    389);
        $bindDn = env('LDAP_BIND_DN',       'cn=admin,dc=unibague,dc=edu,dc=co');
        $bindPw = env('LDAP_BIND_PASSWORD', '');
        $baseDn = env('LDAP_BASE_DN',       'dc=unibague,dc=edu,dc=co');
        $filter = env('LDAP_FILTER',        '(objectClass=person)');
        // Mantis usa mail1 como campo de email en este LDAP
        $emailField = env('LDAP_EMAIL_FIELD', 'mail1');

        if (empty($bindPw) || !function_exists('ldap_connect')) return [];

        $conn = @ldap_connect($host, $port);
        if (!$conn) return [];

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($conn, $bindDn, $bindPw)) {
            ldap_unbind($conn);
            return [];
        }

        $attrs  = ['cn', 'name', 'sn', $emailField, 'uid', 'mail1', 'mail2', 'gacctmail', 'cargo', 'dependencia'];
        $search = @ldap_search($conn, $baseDn, $filter, $attrs, 0, 0, 30);
        if (!$search) {
            ldap_unbind($conn);
            return [];
        }

        $entries = ldap_get_entries($conn, $search);
        ldap_unbind($conn);

        $result = [];
        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $e   = $entries[$i];
            $uid = $e['uid'][0] ?? '';

            // Intentar email en orden de confiabilidad
            $email = '';
            foreach ([$emailField, 'gacctmail', 'mail2', 'mail1'] as $f) {
                $val = $e[strtolower($f)][0] ?? '';
                if (!empty($val) && $val !== '0' && filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $email = $val;
                    break;
                }
            }
            if (empty($email)) continue;

            // Nombre: preferir 'name', luego cn, luego uid
            $nombre = trim(
                $e['name'][0]
                ?? trim(($e['cn'][0] ?? '') . ' ' . ($e['sn'][0] ?? ''))
                ?: $uid
            );

            $result[] = [
                'username'    => $uid ?: explode('@', $email)[0],
                'nombre'      => $nombre ?: $uid,
                'email'       => strtolower($email),
                'cargo'       => $conCargo ? ($e['cargo'][0] ?? null) : null,
                'dependencia' => $conCargo ? ($e['dependencia'][0] ?? null) : null,
            ];
        }

        return $result;
    }

    // GET /pqrs/ldap-cargo?email=xxx — retorna cargo y dependencia de un usuario en el LDAP
    public function ldapCargo(Request $request)
    {
        $email  = strtolower(trim($request->query('email', '')));
        if (empty($email)) return response()->json([]);

        $ldapUsuarios = $this->obtenerUsuariosLdap(true);
        foreach ($ldapUsuarios as $u) {
            if (strtolower($u['email']) === $email) {
                return response()->json([
                    'cargo'       => $u['cargo']       ?? null,
                    'dependencia' => $u['dependencia'] ?? null,
                    'username'    => $u['username']    ?? null,
                ]);
            }
        }
        return response()->json([]);
    }


    public function formData()
    {
        return response()->json([
            'tipos_solicitud'    => MantisService::TIPOS_SOLICITUD,
            'tipos_usuario'      => MantisService::TIPOS_USUARIO,
            'prioridades'        => MantisService::PRIORIDADES,
            'estados_pqr'        => MantisService::ESTADOS_PQR,
            'areas_enrutamiento' => MantisService::AREAS_ENRUTAMIENTO,
        ]);
    }

    // GET /pqrs/lideres — retorna el handler asignado por proyecto en Mantis
    public function lideres()
    {
        $proyectos = [
            'G3'        => 1,
            'FINANCIERA'=> 29,
            'SERVICIOS' => 27,
        ];

        $resultado = [];
        foreach ($proyectos as $nombre => $id) {
            $ch = curl_init(env('MANTIS_BASE_URL') . '/api/rest/projects/' . $id);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => ['Authorization: ' . env('MANTIS_TOKEN')],
            ]);
            $raw  = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($raw, true);
            $categorias = $data['projects'][0]['categories'] ?? [];

            $lideres = [];
            foreach ($categorias as $cat) {
                if (!empty($cat['default_handler'])) {
                    $handler = $cat['default_handler'];
                    $lideres[$handler['id']] = [
                        'id'        => $handler['id'],
                        'name'      => $handler['name'],
                        'real_name' => $handler['real_name'] ?? $handler['name'],
                        'email'     => $handler['email'] ?? null,
                        'categoria' => $cat['name'],
                    ];
                }
            }
            $resultado[$nombre] = array_values($lideres);
        }

        return response()->json($resultado);
    }

    // POST /pqrs — radicar una PQRS
    public function store(Request $request)
    {
        $errors = $this->validarPqr($request);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        try {
            $data = $request->only([
                'nombre', 'email', 'tipo_usuario', 'tipo_solicitud',
                'asunto', 'descripcion', 'area_enrutamiento', 'categoria', 'prioridad',
            ]);

            // radicado viene del Express (opcional, para mostrarlo en el correo)
            $radicado = $request->input('radicado', null);

            $issue = $this->mantisService->crearPqr($data);

            $time = date('Y-m-d H:i:s');
            $fechaLimite = $this->calcularFechaLimite(5);

            DB::table('pqrs')->insert([
                'issue_id'          => $issue['id'],
                'nombre'            => $data['nombre'],
                'email'             => $data['email'],
                'tipo_usuario'      => $data['tipo_usuario'],
                'tipo_solicitud'    => $data['tipo_solicitud'],
                'asunto'            => $data['asunto'],
                'descripcion'       => $data['descripcion'],
                'area_enrutamiento' => $data['area_enrutamiento'],
                'prioridad'         => $data['prioridad'],
                'created_at'        => $time,
                'updated_at'        => $time,
            ]);

            $mailData = array_merge($data, [
                'issue_id'    => $issue['id'],
                'radicado'    => $radicado ?? ('MANTIS-' . $issue['id']),
                'fecha'       => date('d/m/Y H:i'),
                'fecha_limite' => $fechaLimite,
            ]);

            // Correo al usuario que radicó
            try {
                Mail::to($data['email'])->send(new PqrUsuarioNotificacion($mailData));
            } catch (\Exception $mailErr) {
                \Log::warning('No se pudo enviar correo al usuario: ' . $mailErr->getMessage());
            }

            // Correo al líder: tomar el handler asignado en Mantis
            try {
                $liderEmail = $this->mantisService->obtenerEmailHandler($issue['id'])
                    ?? env('PQR_LIDER_EMAIL', 'fspqr@unibague.edu.co');
                Mail::to($liderEmail)->send(new PqrLiderNotificacion($mailData));
            } catch (\Exception $mailErr) {
                \Log::warning('No se pudo enviar correo al líder: ' . $mailErr->getMessage());
            }

            // Correo de copia informativa
            try {
                $copiaEmail = env('PQR_COPIA_EMAIL');
                if ($copiaEmail) {
                    Mail::to($copiaEmail)->send(new PqrCopiaNotificacion($mailData));
                }
            } catch (\Exception $mailErr) {
                \Log::warning('No se pudo enviar correo de copia: ' . $mailErr->getMessage());
            }

            return response()->json([
                'message'      => 'PQRS radicada exitosamente',
                'issue_id'     => $issue['id'],
                'fecha_limite' => $fechaLimite,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // PATCH /pqrs/{issue_id} — actualizar estado, nota, responsable, proyecto y categoría en Mantis
    public function update(Request $request, string $issue_id)
    {
        try {
            $estado      = $request->input('estado');
            $nota        = $request->input('nota');
            $responsable = $request->input('responsable');
            $prioridad   = $request->input('prioridad');
            $project     = $request->input('project');   // ['name' => 'G3']
            $category    = $request->input('category');  // ['name' => 'DESARROLLO']

            $prioridadMap = ['Alta' => 'high', 'Media' => 'normal', 'Baja' => 'low'];
            $body = [];
            $handler = $this->resolverHandlerMantis($responsable);
            $estadoMantis = MantisService::estadoMantisDesdePqr($estado, $handler !== null);

            if ($prioridad)    $body['priority'] = ['name' => $prioridadMap[$prioridad] ?? 'normal'];
            if ($handler)      $body['handler']  = $handler;
            if ($estadoMantis) $body['status']   = ['name' => $estadoMantis];
            if ($project)      $body['project']  = is_array($project) ? $project : ['name' => $project];
            if ($category)     $body['category'] = is_array($category) ? $category : ['name' => $category];

            if (!empty($body)) {
                $this->mantisService->actualizarIssue((int)$issue_id, $body);
            }

            // Agregar nota con el cambio de estado
            if ($nota || $estadoMantis) {
                $texto = $nota ?? "Estado actualizado a: " . MantisService::estadoPqrDesdeMantis($estadoMantis);
                $this->mantisService->agregarNota((int)$issue_id, $texto);
            }

            return response()->json([
                'message'       => 'Issue actualizado en Mantis',
                'estado_mantis' => $estadoMantis,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /pqrs/{issue_id} — eliminar un issue en Mantis
    public function destroy(string $issue_id)
    {
        try {
            $this->mantisService->eliminarIssue((int)$issue_id);

            DB::table('pqrs')->where('issue_id', (int)$issue_id)->delete();

            return response()->json(['message' => 'Issue eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $issue_id)
    {
        try {
            $issue = $this->mantisService->obtenerPqr($issue_id);

            // Mapear prioridad de Mantis (en inglés) a español
            $prioridadMap = [
                'none'       => 'Baja',
                'low'        => 'Baja',
                'normal'     => 'Media',
                'high'       => 'Alta',
                'urgent'     => 'Alta',
                'immediate'  => 'Alta',
            ];
            $prioridadRaw = strtolower($issue['priority']['name'] ?? 'normal');
            $prioridad    = $prioridadMap[$prioridadRaw] ?? 'Media';

            // Extraer notas del issue
            $notas = array_map(function($note) {
                return [
                    'id'         => $note['id'] ?? null,
                    'texto'      => $note['text'] ?? '',
                    'autor'      => $note['reporter']['name'] ?? 'Sistema',
                    'fecha'      => $note['created_at'] ?? null,
                ];
            }, $issue['notes'] ?? []);

            // Extraer historial de cambios
            $historial = array_map(function($h) {
                return [
                    'fecha'      => $h['created_at'] ?? null,
                    'usuario'    => $h['user']['name'] ?? 'Sistema',
                    'tipo'       => $h['type']['name'] ?? '',
                    'campo'      => $h['field']['label'] ?? $h['message'] ?? '',
                    'cambio'     => $h['change'] ?? null,
                    'nota_id'    => $h['note']['id'] ?? null,
                ];
            }, $issue['history'] ?? []);

            return response()->json([
                'id'                  => $issue['id'],
                'asunto'              => $issue['summary'],
                'estado'              => MantisService::estadoPqrDesdeMantis(
                    $issue['status']['name'] ?? null,
                    $issue['status']['label'] ?? null
                ),
                'prioridad'           => $prioridad,
                'area'                => $issue['category']['name'] ?? null,
                'fecha_creacion'      => $issue['created_at'],
                'fecha_actualizacion' => $issue['updated_at'],
                'notas'               => $notas,
                'historial'           => $historial,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    private function calcularFechaLimite(int $diasHabiles): string
    {
        $fecha = new \DateTime();
        $agregados = 0;
        while ($agregados < $diasHabiles) {
            $fecha->modify('+1 day');
            $diaSemana = (int) $fecha->format('N'); // 1=lunes, 7=domingo
            if ($diaSemana < 6) {
                $agregados++;
            }
        }
        return $fecha->format('d/m/Y');
    }

    private function resolverHandlerMantis($responsable): ?array
    {
        if (empty($responsable)) {
            return null;
        }

        if (is_array($responsable)) {
            if (!empty($responsable['id'])) {
                return ['id' => (int)$responsable['id']];
            }

            $nombre = $responsable['username'] ?? $responsable['name'] ?? null;
            return $nombre ? ['name' => trim($nombre)] : null;
        }

        $responsable = trim((string)$responsable);

        if ($responsable === '') {
            return null;
        }

        return is_numeric($responsable)
            ? ['id' => (int)$responsable]
            : ['name' => $responsable];
    }

    protected function validarPqr(Request $request): array
    {
        $errors   = [];
        $required = ['nombre', 'email', 'tipo_usuario', 'tipo_solicitud', 'asunto', 'descripcion', 'area_enrutamiento', 'prioridad'];

        foreach ($required as $field) {
            if (!$request->filled($field)) {
                $errors[] = "El campo {$field} es obligatorio";
            }
        }

        if ($request->filled('email') && !filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }

        if ($request->filled('tipo_solicitud') && !in_array($request->input('tipo_solicitud'), MantisService::TIPOS_SOLICITUD)) {
            $errors[] = 'Tipo de solicitud no válido';
        }

        if ($request->filled('tipo_usuario') && !in_array($request->input('tipo_usuario'), MantisService::TIPOS_USUARIO)) {
            $errors[] = 'Tipo de usuario no válido';
        }

        if ($request->filled('prioridad') && !in_array($request->input('prioridad'), MantisService::PRIORIDADES)) {
            $errors[] = 'Prioridad no válida';
        }

        if ($request->filled('area_enrutamiento') && !array_key_exists($request->input('area_enrutamiento'), MantisService::AREAS_ENRUTAMIENTO)) {
            $errors[] = 'Área de enrutamiento no válida';
        }

        return $errors;
    }

    // POST /pqrs/notificar-necesita-datos
    public function notificarNecesitaDatos(Request $request)
    {
        try {
            $emailUsuario  = $request->input('email_usuario');
            $nombre        = $request->input('nombre');
            $radicado      = $request->input('radicado');
            $tipoSolicitud = $request->input('tipo_solicitud');
            $asunto        = $request->input('asunto');
            $observaciones = $request->input('observaciones', 'Por favor complemente su solicitud con la información requerida.');

            if (!$emailUsuario || !$radicado) {
                return response()->json(['error' => 'Faltan datos requeridos'], 400);
            }

            $mailData = [
                'nombre'         => $nombre,
                'radicado'       => $radicado,
                'tipo_solicitud' => $tipoSolicitud,
                'asunto'         => $asunto,
                'observaciones'  => $observaciones,
            ];

            Mail::to($emailUsuario)->send(new PqrNecesitaDatosNotificacion($mailData));

            return response()->json(['message' => 'Correo enviado al usuario']);
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo necesita datos: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo enviar el correo: ' . $e->getMessage()], 500);
        }
    }

}
