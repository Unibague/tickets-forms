<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrAsignacionNotificacion extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('emails.pqr_asignacion')
            ->subject("PQRS asignada {$this->data['radicado']} - {$this->data['tipo_solicitud']}");
    }
}
