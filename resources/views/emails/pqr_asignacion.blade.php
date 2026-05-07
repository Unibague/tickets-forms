<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f7fb; font-family: Arial, Helvetica, sans-serif; font-size:15px;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7fb; padding: 30px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.10);">

                {{-- Encabezado --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); padding: 32px 36px;">
                        <p style="margin:0; font-size:13px; color:#bfdbfe; letter-spacing:1px; text-transform:uppercase; font-weight:600;">Universidad de Ibagué · Centro de Servicios</p>
                        <h1 style="margin: 8px 0 0; color:#ffffff; font-size:22px; font-weight:700;">PQRS Asignada</h1>
                    </td>
                </tr>

                {{-- Intro --}}
                <tr>
                    <td style="padding: 28px 36px 0;">
                        <p style="margin:0; color:#374151; font-size:15px;">
                            Hola <strong>{{ $data['responsable'] }}</strong>, se te ha asignado la siguiente PQRS para su atención:
                        </p>
                    </td>
                </tr>

                {{-- Tabla de datos --}}
                <tr>
                    <td style="padding: 20px 36px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:8px; overflow:hidden; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding:11px 14px; background:#eff6ff; font-weight:700; color:#1e3a5f; width:45%; border-bottom:1px solid #e5e7eb;">Número de radicado</td>
                                <td style="padding:11px 14px; background:#eff6ff; color:#1e40af; font-weight:700; border-bottom:1px solid #e5e7eb;">{{ $data['radicado'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">ID en sistema de tickets</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">#{{ $data['issue_id'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Tipo de solicitud</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['tipo_solicitud'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Solicitante</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['nombre'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Correo solicitante</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['email'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Prioridad</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['prioridad'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Asunto</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['asunto'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Área responsable</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['area_responsable'] }}</td>
                            </tr>
                            @if(!empty($data['observaciones']))
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Observaciones</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['observaciones'] }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding:11px 14px; background:#fffbeb; font-weight:700; color:#92400e;">Fecha límite</td>
                                <td style="padding:11px 14px; background:#fffbeb; color:#92400e; font-weight:700;">{{ $data['fecha_limite'] }} <span style="font-weight:400; font-size:13px;">(5 días hábiles)</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Botón Ver en Mantis --}}
                <tr>
                    <td style="padding: 0 36px 28px; text-align:center;">
                        <a href="https://tickets.unibague.edu.co/tickets/my_view_page.php"
                           style="display:inline-block; background:#2563eb; color:#ffffff; padding:13px 28px; border-radius:8px; text-decoration:none; font-weight:700; font-size:15px;">
                            Ver en Mantis
                        </a>
                    </td>
                </tr>

                {{-- Pie --}}
                <tr>
                    <td style="background:#f8fafc; border-top:1px solid #e5e7eb; padding: 20px 36px; text-align:center;">
                        <p style="margin:0; font-size:13px; color:#6b7280;">
                            Este mensaje es generado automáticamente por el sistema de PQRS.<br>
                            <strong style="color:#374151;">Universidad de Ibagué — Centro de Servicios</strong>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
