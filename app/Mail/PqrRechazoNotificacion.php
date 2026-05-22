<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrRechazoNotificacion extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('emails.pqr_rechazo')
            ->subject("Tu solicitud ha sido rechazada - {$this->data['radicado']}");
    }
}
