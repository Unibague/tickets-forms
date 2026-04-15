<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body style="font-size: 15px; background-color: #ffffff;">
<section style="width:80%; padding: 5% 10%">
    <div style="background: #F0F9FE; padding: 5% 5%; border-radius: 15px; box-shadow: 0 0 5px #3d4852">

        <h3 style="color: #2c3e50; margin-bottom: 10px;"> Tu PQRS fue radicada exitosamente</h3>

        <p>Estimado(a) <strong>{{ $data['nombre'] }}</strong>,</p>

        <p>
            Hemos recibido tu solicitud de tipo <strong>{{ $data['tipo_solicitud'] }}</strong> con el número de radicado
            <strong>{{ $data['radicado'] }}</strong> (ID Mantis: #{{ $data['issue_id'] }}). A continuación el resumen:
        </p>

        <table style="width:100%; border-collapse: collapse; margin-top: 15px;">
            <tr style="background:#dbeafe;">
                <td style="padding:8px; font-weight:bold; width:40%;">Número de radicado</td>
                <td style="padding:8px;"><strong>{{ $data['radicado'] }}</strong></td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">ID en sistema de tickets</td>
                <td style="padding:8px;">#{{ $data['issue_id'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Tipo de solicitud</td>
                <td style="padding:8px;">{{ $data['tipo_solicitud'] }}</td>
            </tr>
            <tr style="background:#f8fafc;">
                <td style="padding:8px; font-weight:bold;">Área responsable</td>
                <td style="padding:8px;">{{ $data['area_enrutamiento'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Asunto</td>
                <td style="padding:8px;">{{ $data['asunto'] }}</td>
            </tr>
            <tr style="background:#f8fafc;">
                <td style="padding:8px; font-weight:bold;">Fecha de radicado</td>
                <td style="padding:8px;">{{ $data['fecha'] }}</td>
            </tr>
            <tr style="background:#fff3cd;">
                <td style="padding:8px; font-weight:bold;">Fecha límite de respuesta</td>
                <td style="padding:8px;"><strong>{{ $data['fecha_limite'] }}</strong> (5 días hábiles)</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">
            Puedes consultar el estado de tu solicitud en cualquier momento haciendo clic en el siguiente botón:
        </p>

        <div style="text-align: center; margin-top: 20px;">
            <a href="https://servicios.unibague.edu.co/pqrs/consulta?radicado={{ $data['radicado'] }}"
               style="background-color:#2563eb; color:#fff; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold;">
                Consultar estado de mi PQRS
            </a>
        </div>

        <div style="margin-top:15px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; text-align:center;">
            <p style="margin:0; font-size:13px; color:#166534;">Tu número de radicado es:</p>
            <p style="margin:4px 0 0; font-size:20px; font-weight:bold; font-family:monospace; color:#1e40af; letter-spacing:2px;">{{ $data['radicado'] }}</p>
            <p style="margin:4px 0 0; font-size:11px; color:#6b7280;">Guárdalo para consultar el estado de tu solicitud</p>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 13px;">
            Si tienes dudas, puedes responder este correo o comunicarte con nosotros.<br>
            <strong>Universidad de Ibagué — Centro de Servicios</strong>
        </p>

    </div>
</section>
</body>
</html>
