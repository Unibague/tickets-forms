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
                    <td style="background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%); padding: 32px 36px;">
                        <p style="margin:0; font-size:13px; color:#fecaca; letter-spacing:1px; text-transform:uppercase; font-weight:600;">Universidad de Ibagué · Centro de Servicios</p>
                        <h1 style="margin: 8px 0 0; color:#ffffff; font-size:22px; font-weight:700;">Solicitud rechazada</h1>
                    </td>
                </tr>

                {{-- Saludo --}}
                <tr>
                    <td style="padding: 28px 36px 0;">
                        <p style="margin:0; color:#374151; font-size:15px;">
                            Estimado(a) <strong>{{ $data['nombre'] }}</strong>, lamentamos informarte que tu solicitud radicada en el sistema de PQRS ha sido rechazada por el equipo administrativo.
                        </p>
                    </td>
                </tr>

                {{-- Motivo destacado --}}
                <tr>
                    <td style="padding: 20px 36px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px;">
                            <tr>
                                <td style="padding: 16px 20px;">
                                    <p style="margin:0; font-size:13px; color:#991b1b; font-weight:700;"> Motivo del rechazo</p>
                                    <p style="margin:8px 0 0; font-size:14px; color:#7f1d1d; line-height:1.6;">
                                        {{ $data['motivo'] }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Datos de la PQRS --}}
                <tr>
                    <td style="padding: 20px 36px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:8px; overflow:hidden; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding:11px 14px; background:#eff6ff; font-weight:700; color:#1e3a5f; width:45%; border-bottom:1px solid #e5e7eb;">Número de radicado</td>
                                <td style="padding:11px 14px; background:#eff6ff; color:#1e40af; font-weight:700; border-bottom:1px solid #e5e7eb;">{{ $data['radicado'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Tipo de solicitud</td>
                                <td style="padding:11px 14px; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['tipo_solicitud'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; background:#f9fafb; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb;">Asunto</td>
                                <td style="padding:11px 14px; background:#f9fafb; color:#374151; border-bottom:1px solid #e5e7eb;">{{ $data['asunto'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:11px 14px; font-weight:600; color:#374151;">Estado</td>
                                <td style="padding:11px 14px; color:#dc2626; font-weight:700;">Rechazada</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Mensaje adicional --}}
                <tr>
                    <td style="padding: 0 36px 28px;">
                        <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.6;">
                            Si consideras que esta decisión es incorrecta o deseas presentar una nueva solicitud con la información corregida, puedes ingresar al portal de PQRS.
                        </p>
                        <div style="text-align:center; margin-top:20px;">
                            <a href="{{ env('PQRS_APP_URL', 'http://localhost:8091') }}"
                               style="display:inline-block; background:#1d4ed8; color:#ffffff; padding:13px 28px; border-radius:8px; text-decoration:none; font-weight:700; font-size:15px;">
                                Ir al portal de PQRS
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- Pie de página --}}
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
