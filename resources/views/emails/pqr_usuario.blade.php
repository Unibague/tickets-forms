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
                    <td style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); padding: 32px 36px;">
                        <p style="margin:0; font-size:13px; color:#bfdbfe; letter-spacing:1px; text-transform:uppercase; font-weight:600;">Universidad de Ibagué</p>
                        <h1 style="margin: 8px 0 0; color:#ffffff; font-size:22px; font-weight:700;">Tu PQRS fue radicada exitosamente</h1>
                    </td>
                </tr>

                {{-- Saludo --}}
                <tr>
                    <td style="padding: 28px 36px 0;">
                        <p style="margin:0; color:#374151; font-size:15px;">
                            Estimado(a) <strong>{{ $data['nombre'] }}</strong>, hemos recibido tu solicitud. A continuación el resumen:
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
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Asunto</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['asunto'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Fecha de radicado</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['fecha'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#fffbeb; font-weight:700; color:#92400e;">Fecha límite de respuesta</td>
                                <td style="padding:11px 14px; background:#fffbeb; color:#92400e; font-weight:700;">{{ $data['fecha_limite'] }} <span style="font-weight:400; font-size:13px;">(5 días hábiles)</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Número de radicado destacado --}}
                <tr>
                    <td style="padding: 0 36px 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;">
                            <tr>
                                <td style="padding: 18px; text-align:center;">
                                    <p style="margin:0; font-size:13px; color:#166534;">Tu número de radicado es:</p>
                                    <p style="margin:6px 0 4px; font-size:22px; font-weight:700; font-family:monospace; color:#1e40af; letter-spacing:3px;">{{ $data['radicado'] }}</p>
                                    <p style="margin:0; font-size:12px; color:#6b7280;">Guárdalo para consultar el estado de tu solicitud</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Texto + botón --}}
                <tr>
                    <td style="padding: 0 36px 24px; text-align:center;">
                        <p style="margin:0 0 16px; color:#374151; font-size:14px;">
                            Puedes consultar el estado de tu solicitud en cualquier momento:
                        </p>
                        <a href="https://servicios.unibague.edu.co/pqrs/consulta?radicado={{ $data['radicado'] }}"
                           style="display:inline-block; background:#2563eb; color:#ffffff; padding:13px 28px; border-radius:8px; text-decoration:none; font-weight:700; font-size:15px;">
                            Consultar estado de mi PQRS
                        </a>
                    </td>
                </tr>

                {{-- Pie de página --}}
                <tr>
                    <td style="background:#f8fafc; border-top:1px solid #e5e7eb; padding: 20px 36px; text-align:center;">
                        <p style="margin:0; font-size:13px; color:#6b7280;">
                            Si tienes dudas puedes responder este correo o comunicarte con nosotros.<br>
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
