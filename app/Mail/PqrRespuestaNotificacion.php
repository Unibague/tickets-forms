<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrRespuestaNotificacion extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('emails.pqr_respuesta')
            ->subject("Respuesta a su {$this->data['tipo_solicitud']} · {$this->data['radicado']}");
    }
}
