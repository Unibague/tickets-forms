<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrNecesitaDatosNotificacion extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('emails.pqr_necesita_datos')
            ->subject("Se requiere información adicional - {$this->data['radicado']}");
    }
}
