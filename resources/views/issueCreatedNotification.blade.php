<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body style="font-size: 15px; background-color: #ffffff">

<section style="width:80%; padding: 5% 10%">
{{--    <h2 style="text-align: center"> Centro de Servicios </h2>--}}
    <div style="background: #F0F9FE; padding: 5% 5%; border-radius: 15px; box-shadow: 0 0 5px #3d4852">

        @if($data['project_name'] === 'FINANCIERA')

        Estimado (a), <br>

        Hemos recibido exitosamente tu solicitud de “Radicación Facturas Electrónicas, Cuentas
        de Cobro y otros documentos”, con ID <strong>#{{$data['issue_id']}} </strong>. Esta será tramitada teniendo en cuenta lo siguiente:

            <ul>
                <li> <strong>Facturas Electrónicas</strong>: Es necesario responder dando clic en link
                    <span style="color: #3498db">“Seguimiento a solicitud” </span> y dar aprobación como supervisor del contrato.
                    Adjunta el formato de “Autorización de Contratación y Gastos”, que contenga todas las firmas.
                    Si la factura electrónica es de persona natural adjuntar la
                    planilla de seguridad social como independiente y formato de “Retención
                    Rentas de Trabajo sin Vínculo Laboral”. <br>
                    <span style="color: #3498db"> ¡¡¡ Importante !!! <br>
                        Pasados tres días calendario, sin respuesta, las facturas electrónicas serán
                        rechazadas en la plataforma de la Dian. </span>
                </li>
                <li>
                    <strong>Cuentas de Cobro</strong>: En caso de necesitar alguna corrección o documento
                    adicional, se le notificará al correo electrónico diligenciado en la solicitud.
                    Recuerda, debes entregar en la oficina de Dirección Administrativa de forma física,
                    la cuenta de cobro con firma autógrafa, adjuntando la planilla de seguridad social
                    como trabajador independiente, RUT con fecha de impresión vigente, Certificación
                    Bancaria y formato de “Retención Rentas de Trabajo sin Vínculo Laboral”. <br>
                </li>
                <li>
                    <strong>Otros documentos (Pagos y descuentos de Nómina, Préstamos y
                        Devoluciones, Pruebas Saber Pro, Solicitudes de giro y Monitorias):</strong>:
                    Si se requiere alguna corrección,
                    se le notificará al correo electrónico diligenciado en la solicitud. <br> <br>
                </li>

                Agradecemos su atención.

            </ul>

        @else

            Estimado (a),<br><br>

            Se ha creado satisfactoriamente su solicitud para el servicio <strong> {{$data['issue_name']}} </strong>
            con ID <strong>#{{$data['issue_id']}}</strong>, la cual será asignada próximamente para ser atendida. <br><br>

        @endif

        Si desea consultar el estado de su solicitud, dejar observaciones, comentarios o adjuntar información adicional, por favor,
        haga clic en el siguiente botón.

        <div style="text-align: center">
            <a href="https://servicios.unibague.edu.co/tickets" ><img src="{{$message->embed(public_path().'/images/follow.png')}}"></a>
        </div>

        </div>
</section>

</body>

</html>
