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
                    <td style="background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%); padding: 32px 36px;">
                        <p style="margin:0; font-size:13px; color:#bfdbfe; letter-spacing:1px; text-transform:uppercase; font-weight:600;">Universidad de Ibagué · Centro de Servicios</p>
                        <h1 style="margin: 8px 0 0; color:#ffffff; font-size:22px; font-weight:700;">Respuesta a su solicitud</h1>
                    </td>
                </tr>

                {{-- Saludo --}}
                <tr>
                    <td style="padding: 28px 36px 0;">
                        <p style="margin:0; color:#374151; font-size:15px;">
                            Estimado(a) <strong>{{ $data['nombre'] }}</strong>, el equipo responsable ha dado respuesta a su solicitud radicada en el sistema de PQRS.
                        </p>
                    </td>
                </tr>

                {{-- Respuesta destacada --}}
                <tr>
                    <td style="padding: 20px 36px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff; border:1px solid #93c5fd; border-radius:10px;">
                            <tr>
                                <td style="padding: 16px 20px;">
                                    <p style="margin:0; font-size:13px; color:#1e3a5f; font-weight:700;">Respuesta del responsable</p>
                                    <p style="margin:8px 0 0; font-size:14px; color:#1e40af; line-height:1.6; white-space:pre-line;">{{ $data['respuesta'] }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Datos de la PQRS --}}
                <tr>
                    <td style="padding: 20px 36px 0;">
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
                                <td style="padding:11px 14px; color:#16a34a; font-weight:700;">{{ $data['estado'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Archivos adjuntos (si los hay) --}}
                @if (!empty($data['archivos']))
                <tr>
                    <td style="padding: 16px 36px 0;">
                        <p style="margin:0 0 10px; font-size:13px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px;">Archivos adjuntos</p>
                        @foreach ($data['archivos'] as $archivo)
                        @php
                            $nombre = is_array($archivo) ? $archivo['nombre'] : $archivo;
                            $url    = is_array($archivo) ? $archivo['url'] : null;
                            $ext    = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                            $esImagen = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $esVideo  = in_array($ext, ['mp4','webm']);
                        @endphp
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                            <tr>
                                <td style="padding:10px 14px; background:#f9fafb; display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:18px;">{{ $esVideo ? '🎬' : ($esImagen ? '🖼️' : '📎') }}</span>
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank"
                                           style="color:#1d4ed8; font-weight:600; font-size:14px; text-decoration:none;">
                                            {{ $nombre }}
                                        </a>
                                        &nbsp;
                                        <a href="{{ $url }}" target="_blank"
                                           style="display:inline-block; background:#1d4ed8; color:#fff; padding:4px 12px; border-radius:5px; font-size:12px; text-decoration:none; font-weight:600;">
                                            {{ $esVideo ? 'Ver video' : ($esImagen ? 'Ver imagen' : 'Abrir') }}
                                        </a>
                                    @else
                                        <span style="font-size:14px; color:#374151;">{{ $nombre }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($url && $esImagen)
                            <tr>
                                <td style="padding:0 14px 12px; background:#f9fafb; text-align:center;">
                                    <img src="{{ $url }}" alt="{{ $nombre }}"
                                         style="max-width:100%; max-height:300px; border-radius:6px; border:1px solid #e5e7eb;" />
                                </td>
                            </tr>
                            @endif
                        </table>
                        @endforeach
                    </td>
                </tr>
                @endif

                {{-- Botón consultar --}}
                <tr>
                    <td style="padding: 24px 36px 28px; text-align:center;">
                        <p style="margin:0 0 16px; color:#374151; font-size:14px;">
                            Puedes consultar el detalle completo de tu solicitud en el portal:
                        </p>
                        <a href="{{ env('PQRS_APP_URL', 'http://localhost:8091') }}/consulta?radicado={{ $data['radicado'] }}"
                           style="display:inline-block; background:#1d4ed8; color:#ffffff; padding:13px 28px; border-radius:8px; text-decoration:none; font-weight:700; font-size:15px;">
                            Ver mi solicitud
                        </a>
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
