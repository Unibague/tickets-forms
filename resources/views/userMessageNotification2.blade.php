<!DOCTYPE html>
<html>
<head>
    <title>Evaluación de Desempeño Administrativos EDA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body style="font-size: 15px; background-color: #ffffff">

<section style="width:80%; padding: 5% 10%">
{{--    <h2 style="text-align: center"> Centro de Servicios </h2>--}}
    <div style="background: #F0F9FE; padding: 5% 5%; border-radius: 15px; box-shadow: 0 0 5px #3d4852">

        <strong style="margin-bottom: 20px">Actualización de su solicitud con ID #{{$data['issue_id']}}</strong><br><br>

        Reciba un cordial saludo, <br><br>

        Se ha realizado una actualización en su solicitud de servicio. El mensaje que ha suministrado la persona encargada para solucionar su requerimiento es el siguiente: <br><br>

        <div style="text-align: center">
            <textarea> {{$data['message']}} </textarea> <br><br>
        </div>
        Si desea consultar el estado de su solicitud, dejar observaciones, comentarios o adjuntar información adicional, por favor,
        haga clic en el siguiente botón.


        <div style="display: flex; align-items: center; justify-content: center">
            <a href="https://servicios.unibague.edu.co/tickets" ><img src="{{ url('images/follow.png') }}"> </a>
        </div>

        <p> Cordialmente,<br>
            Universidad de Ibagué
        </p>


        </div>
    </div>
</section>

</body>

</html>
