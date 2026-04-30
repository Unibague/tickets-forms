<?php

namespace App\Services;

use App\MantisApi;

class MantisService
{
    private MantisApi $mantisApi;

    const TIPOS_SOLICITUD = ['Petición', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación'];

    const TIPOS_USUARIO = ['Estudiante', 'Docente', 'Administrativo', 'Egresado', 'Usuario externo'];

    const PRIORIDADES = ['Alta', 'Media', 'Baja'];

    const ESTADOS_PQR = ['Recibido', 'En revisión', 'Se necesitan más datos', 'Resuelto', 'Cerrado'];

    private const ESTADO_MANTIS_POR_PQR = [
        'recibido' => 'new',
        'nuevo' => 'new',
        'new' => 'new',
        'en revision' => 'assigned',
        'en revisión' => 'assigned',
        'asignado' => 'assigned',
        'assigned' => 'assigned',
        'se necesitan mas datos' => 'feedback',
        'se necesitan más datos' => 'feedback',
        'necesita mas datos' => 'feedback',
        'necesita más datos' => 'feedback',
        'pendiente informacion' => 'feedback',
        'pendiente información' => 'feedback',
        'feedback' => 'feedback',
        'resuelto' => 'resolved',
        'resolved' => 'resolved',
        'cerrado' => 'closed',
        'closed' => 'closed',
    ];

    private const ESTADO_PQR_POR_MANTIS = [
        'new' => 'Recibido',
        'acknowledged' => 'En revisión',
        'confirmed' => 'En revisión',
        'assigned' => 'En revisión',
        'feedback' => 'Se necesitan más datos',
        'resolved' => 'Resuelto',
        'closed' => 'Cerrado',
    ];

    // Área → proyecto Mantis + subcategorías disponibles
    const AREAS_ENRUTAMIENTO = [
        'Financiero'             => ['project' => 'FINANCIERA', 'categorias' => ['FACT. ELEC Y CTAS. COBRO', 'General']],
        'Tesorería'              => ['project' => 'FINANCIERA', 'categorias' => ['General']],
        'Registro académico'     => ['project' => 'SERVICIOS',  'categorias' => ['General']],
        'Bienestar universitario'=> ['project' => 'SERVICIOS',  'categorias' => ['General']],
        'Sistemas'               => ['project' => 'G3',         'categorias' => ['CENTRO DE COMPUTO', 'DESARROLLO', 'DIRECCION G3', 'MANTENIMIENTO', 'REDES']],
        'Consultorio Jurídico'   => ['project' => 'SERVICIOS',  'categorias' => ['General']],
        'Secretaria General'     => ['project' => 'SERVICIOS',  'categorias' => ['General']],
    ];

    public function __construct()
    {
        $this->mantisApi = new MantisApi(
            env('MANTIS_BASE_URL', 'https://tickets.unibague.edu.co/tickets'),
            env('MANTIS_TOKEN', '')
        );
    }

    public function crearPqr(array $data): array
    {
        $area = self::AREAS_ENRUTAMIENTO[$data['area_enrutamiento']] ?? ['project' => 'SERVICIOS', 'categorias' => ['General']];

        // Mantis espera la prioridad en inglés
        $prioridadMap = ['Alta' => 'high', 'Media' => 'normal', 'Baja' => 'low'];
        $prioridad    = $prioridadMap[$data['prioridad']] ?? 'normal';

        $body = [
            'summary'     => "[PQRS][{$data['tipo_solicitud']}] {$data['asunto']} - {$data['email']}",
            'description' => $this->buildDescription($data),
            'category'    => ['name' => $data['categoria'] ?? $area['categorias'][0]],
            'project'     => ['name' => $area['project']],
            'priority'    => ['name' => $prioridad],
        ];

        $response    = $this->mantisApi->createIssueRaw($body);
        $issueObject = json_decode($response, true);

        if (!isset($issueObject['issue']['id'])) {
            throw new \RuntimeException('No se pudo crear el issue en Mantis: ' . $response);
        }

        return $issueObject['issue'];
    }

    public static function estadoMantisDesdePqr(?string $estado, bool $tieneResponsable = false): ?string
    {
        if ($estado === null || trim($estado) === '') {
            return $tieneResponsable ? 'assigned' : null;
        }

        $estadoNormalizado = self::normalizarEstado($estado);

        if ($estadoNormalizado === 'todos los estados') {
            return null;
        }

        if (!isset(self::ESTADO_MANTIS_POR_PQR[$estadoNormalizado])) {
            throw new \InvalidArgumentException(
                'Estado de PQRS no válido. Use: ' . implode(', ', self::ESTADOS_PQR)
            );
        }

        $estadoMantis = self::ESTADO_MANTIS_POR_PQR[$estadoNormalizado];

        if ($tieneResponsable && $estadoMantis === 'new') {
            return 'assigned';
        }

        return $estadoMantis;
    }

    public static function estadoPqrDesdeMantis(?string $statusName, ?string $statusLabel = null): string
    {
        $estadoNormalizado = self::normalizarEstado($statusName ?? '');

        if (isset(self::ESTADO_PQR_POR_MANTIS[$estadoNormalizado])) {
            return self::ESTADO_PQR_POR_MANTIS[$estadoNormalizado];
        }

        $labelNormalizado = self::normalizarEstado($statusLabel ?? '');

        if (isset(self::ESTADO_MANTIS_POR_PQR[$labelNormalizado])) {
            return self::ESTADO_PQR_POR_MANTIS[self::ESTADO_MANTIS_POR_PQR[$labelNormalizado]];
        }

        return $statusLabel ?: ($statusName ?: 'Recibido');
    }

    public function eliminarIssue(int $issueId): void
    {
        $response = $this->mantisApi->deleteIssue($issueId);

        // DELETE devuelve 204 sin cuerpo; curl_exec retorna "" o false en error
        if ($response === false) {
            throw new \RuntimeException('No se pudo conectar con Mantis al eliminar el issue.');
        }

        if (trim($response) !== '') {
            $decoded = json_decode($response, true);
            if (isset($decoded['code'])) {
                throw new \RuntimeException('Mantis rechazó la eliminación: ' . ($decoded['message'] ?? $response));
            }
        }
    }

    public function actualizarIssue(int $issueId, array $body): array
    {
        $response = $this->mantisApi->updateIssueRaw($issueId, $body);
        $decoded = json_decode($response, true);

        if (isset($decoded['code'])) {
            throw new \RuntimeException('No se pudo actualizar el issue en Mantis: ' . ($decoded['message'] ?? $response));
        }

        return is_array($decoded) ? $decoded : [];
    }

    // Obtener el email del handler (líder asignado) de un issue
    public function obtenerEmailHandler(int $issueId): ?string
    {
        $raw      = $this->mantisApi->getIssueById($issueId);
        $response = json_decode($raw, true);
        return $response['issues'][0]['handler']['email'] ?? null;
    }

    // Agregar nota a un issue via SOAP
    public function agregarNota(int $issueId, string $texto): void
    {
        $soapUrl = env('MANTIS_SOAP_URL');
        $user    = env('MANTIS_SOAP_USER');
        $pass    = env('MANTIS_SOAP_PASS');

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
             . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
             . '<soap:Body>'
             . '<mc_issue_note_add xmlns="http://futureware.biz/mantisconnect">'
             . '<username>' . htmlspecialchars($user) . '</username>'
             . '<password>' . htmlspecialchars($pass) . '</password>'
             . '<issue_id>' . $issueId . '</issue_id>'
             . '<note><text>' . htmlspecialchars($texto) . '</text><view_state><name>public</name></view_state></note>'
             . '</mc_issue_note_add>'
             . '</soap:Body>'
             . '</soap:Envelope>';

        $ch = curl_init($soapUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://futureware.biz/mantisconnect/mc_issue_note_add"',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public function obtenerPqr(string $issueId): array
    {
        $raw = $this->mantisApi->getIssueById((int)$issueId);
        $raw = str_replace(["\r\n", "\r", "\n"], ' ', $raw);
        $response = json_decode($raw, true);

        if (isset($response['code']) || empty($response['issues'])) {
            throw new \RuntimeException('Issue no encontrado');
        }

        return $response['issues'][0];
    }

    private function buildDescription(array $data): string
    {
        return implode("\n", [
            "Tipo de usuario: {$data['tipo_usuario']}",
            "Tipo de solicitud: {$data['tipo_solicitud']}",
            "Nombre: {$data['nombre']}",
            "Email: {$data['email']}",
            "Área: {$data['area_enrutamiento']}",
            "Categoría: " . ($data['categoria'] ?? 'General'),
            "Prioridad: {$data['prioridad']}",
            "Descripción:\n{$data['descripcion']}",
        ]);
    }

    private static function normalizarEstado(string $estado): string
    {
        $estado = trim($estado);

        // Normalize NFC if intl extension available
        if (class_exists('\Normalizer')) {
            $estado = \Normalizer::normalize($estado, \Normalizer::NFC) ?: $estado;
        }

        $estado = strtr($estado, [
            // NFC precompuesto
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            // NFD: eliminar marcas combinadoras (U+0301 agudo, U+0308 diéresis, U+0303 tilde)
            "\xCC\x81" => '', "\xCC\x88" => '', "\xCC\x83" => '',
        ]);

        $estado = mb_strtolower(str_replace(['_', '-'], ' ', $estado), 'UTF-8');

        return preg_replace('/\s+/', ' ', $estado) ?? $estado;
    }
}
