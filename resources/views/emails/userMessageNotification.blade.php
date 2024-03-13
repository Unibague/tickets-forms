@component('mail::message')
    # Actualización de su solicitud con ID \#{{$issueId}}

    Apreciado usuario.

    Se ha realizado una actualización en su solicitud de servicio. El mensaje que ha suministrado la persona encargada para solucionar su requerimiento es el siguiente:

    "{{$message}}"

    Si desea añadir un comentario, por favor ingrese a https://servicios.unibague.edu.co/tickets y dé click al botón "Enviar comentario" que corresponde con el ID de la solicitud.
@endcomponent
