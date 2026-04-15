<?php

namespace App\Services;

use App\MantisApi;

class MantisService
{
    private MantisApi $mantisApi;

    const TIPOS_SOLICITUD = ['Petición', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación'];

    const TIPOS_USUARIO = ['Estudiante', 'Docente', 'Administrativo', 'Egresado', 'Usuario externo'];

    const PRIORIDADES = ['Alta', 'Media', 'Baja'];

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

        $body = [
            'summary'     => "[PQRS][{$data['tipo_solicitud']}] {$data['asunto']} - {$data['email']}",
            'description' => $this->buildDescription($data),
            'category'    => ['name' => $data['categoria'] ?? $area['categorias'][0]],
            'project'     => ['name' => $area['project']],
            'priority'    => ['name' => $data['prioridad']],
        ];

        $response    = $this->mantisApi->createIssueRaw($body);
        $issueObject = json_decode($response, true);

        if (!isset($issueObject['issue']['id'])) {
            throw new \RuntimeException('No se pudo crear el issue en Mantis: ' . $response);
        }

        return $issueObject['issue'];
    }

    // Obtener el email del handler (líder asignado) de un issue
    public function obtenerEmailHandler(int $issueId): ?string
    {
        $raw      = $this->mantisApi->getIssueById($issueId);
        $response = json_decode($raw, true);

        return $response['issues'][0]['handler']['email'] ?? null;
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
}
