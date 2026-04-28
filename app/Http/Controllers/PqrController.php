<?php

namespace App\Http\Controllers;

use App\Mail\PqrLiderNotificacion;
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

    // GET /pqrs/usuarios — todos los usuarios via SOAP mc_project_get_users con cache 1h
    public function usuarios()
    {
        $resultado = (function () {
            $soapUrl  = env('MANTIS_SOAP_URL');
            $user     = env('MANTIS_SOAP_USER');
            $pass     = env('MANTIS_SOAP_PASS');
            $vistos   = [];
            $usuarios = [];

            // project_id=0 trae todos los usuarios del sistema
            $proyectos = [0];

            foreach ($proyectos as $projectId) {
                $xml = '<?xml version="1.0" encoding="utf-8"?>'
                     . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
                     . '<soap:Body>'
                     . '<mc_project_get_users xmlns="http://futureware.biz/mantisconnect">'
                     . '<username>' . htmlspecialchars($user) . '</username>'
                     . '<password>' . htmlspecialchars($pass) . '</password>'
                     . '<project_id>' . $projectId . '</project_id>'
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

                if ($code !== 200 || !$raw) continue;

                // Usar DOMDocument para parsear la respuesta SOAP
                libxml_use_internal_errors(true);
                $doc = new \DOMDocument();
                if (!$doc->loadXML($raw)) continue;
                $nodeList = $doc->getElementsByTagName('item');
                $items    = iterator_to_array($nodeList);
                foreach ($items as $item) {
                    $id    = (int)($item->getElementsByTagName('id')->item(0)->nodeValue ?? 0);
                    $email = $item->getElementsByTagName('email')->item(0)->nodeValue ?? '';
                    $name  = $item->getElementsByTagName('name')->item(0)->nodeValue ?? '';
                    $rname = $item->getElementsByTagName('real_name')->item(0)->nodeValue ?? '';
                    if (empty($email) || empty($name) || isset($vistos[$email])) continue;
                    $vistos[$email] = true;
                    $usuarios[] = [
                        'id'       => $id,
                        'username' => $name,
                        'nombre'   => !empty($rname) ? $rname : $name,
                        'email'    => $email,
                    ];
                }
            }

            usort($usuarios, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
            return array_values($usuarios);
        })();
        return response()->json($resultado);
    }

    
    // GET /pqrs/form-data
    public function formData()
    {
        return response()->json([
            'tipos_solicitud'    => MantisService::TIPOS_SOLICITUD,
            'tipos_usuario'      => MantisService::TIPOS_USUARIO,
            'prioridades'        => MantisService::PRIORIDADES,
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

            return response()->json([
                'message'      => 'PQRS radicada exitosamente',
                'issue_id'     => $issue['id'],
                'fecha_limite' => $fechaLimite,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // PATCH /pqrs/{issue_id} — actualizar estado, nota y responsable en Mantis
    public function update(Request $request, string $issue_id)
    {
        try {
            $estado      = $request->input('estado');
            $nota        = $request->input('nota');
            $responsable = $request->input('responsable');
            $prioridad   = $request->input('prioridad');

            $prioridadMap = ['Alta' => 'high', 'Media' => 'normal', 'Baja' => 'low'];
            $estadoMap    = ['Cerrado' => 'closed', 'Resuelto' => 'resolved'];
            $body = [];
            if ($prioridad)              $body['priority'] = ['name' => $prioridadMap[$prioridad] ?? 'normal'];
            if ($responsable)            $body['handler']  = ['name' => $responsable];
            if (isset($estadoMap[$estado])) $body['status'] = ['name' => $estadoMap[$estado]];

            if (!empty($body)) {
                $ch = curl_init(env('MANTIS_BASE_URL') . '/api/rest/issues/' . $issue_id);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CUSTOMREQUEST  => 'PATCH',
                    CURLOPT_POSTFIELDS     => json_encode($body),
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: ' . env('MANTIS_TOKEN'),
                        'Content-Type: application/json',
                    ],
                ]);
                curl_exec($ch);
                curl_close($ch);
            }

            // Agregar nota con el cambio de estado
            if ($nota || $estado) {
                $texto = $nota ?? "Estado actualizado a: {$estado}";
                $this->mantisService->agregarNota((int)$issue_id, $texto);
            }

            return response()->json(['message' => 'Issue actualizado en Mantis']);
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
                'estado'              => $issue['status']['label'] ?? $issue['status']['name'],
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
}
