@component('mail::message')
    # Actualización de su solicitud con ID \#{{$issueId}}

    Apreciado usuario.

    Se ha realizado una actualización en su solicitud de servicio. El mensaje que ha suministrado el técnico encargado de la solución de su requerimiento es el siguiente:

    "{{$message}}"

    Si desea añadir un comentario, por favor ingrese a https://servicios.unibague.edu.co/tickets y de clic al botón "Enviar comentario" que corresponde con el ID de la solicitud.
@endcomponent
