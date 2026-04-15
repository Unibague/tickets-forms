<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body style="font-size: 15px; background-color: #ffffff;">
<section style="width:80%; padding: 5% 10%">
    <div style="background: #F0F9FE; padding: 5% 5%; border-radius: 15px; box-shadow: 0 0 5px #3d4852">

        <h3 style="color: #2c3e50; margin-bottom: 20px;"> Nueva PQRS Radicada</h3>

        <p>Se ha radicado una nueva PQRS en el sistema. A continuación el detalle:</p>

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
                <td style="padding:8px; font-weight:bold;">Tipo de usuario</td>
                <td style="padding:8px;">{{ $data['tipo_usuario'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Nombre del solicitante</td>
                <td style="padding:8px;">{{ $data['nombre'] }}</td>
            </tr>
            <tr style="background:#f8fafc;">
                <td style="padding:8px; font-weight:bold;">Correo del solicitante</td>
                <td style="padding:8px;">{{ $data['email'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Área responsable</td>
                <td style="padding:8px;">{{ $data['area_enrutamiento'] }}</td>
            </tr>
            <tr style="background:#f8fafc;">
                <td style="padding:8px; font-weight:bold;">Prioridad</td>
                <td style="padding:8px;">{{ $data['prioridad'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Asunto</td>
                <td style="padding:8px;">{{ $data['asunto'] }}</td>
            </tr>
            <tr style="background:#f8fafc;">
                <td style="padding:8px; font-weight:bold;">Descripción</td>
                <td style="padding:8px;">{{ $data['descripcion'] }}</td>
            </tr>
            <tr>
                <td style="padding:8px; font-weight:bold;">Fecha de radicado</td>
                <td style="padding:8px;">{{ $data['fecha'] }}</td>
            </tr>
            <tr style="background:#fff3cd;">
                <td style="padding:8px; font-weight:bold;">Fecha límite de respuesta (5 días hábiles)</td>
                <td style="padding:8px;"><strong>{{ $data['fecha_limite'] }}</strong></td>
            </tr>
        </table>

        <div style="text-align: center; margin-top: 30px;">
            <a href="https://tickets.unibague.edu.co/tickets/view_all_bug_page.php"
               style="background-color:#2563eb; color:#fff; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold;">
                Ver en Mantis
            </a>
        </div>

    </div>
</section>
</body>
</html>
