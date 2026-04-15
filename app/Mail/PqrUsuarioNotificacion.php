<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrUsuarioNotificacion extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('emails.pqr_usuario')
            ->subject("Tu PQRS fue radicada exitosamente - {$this->data['radicado']}");
    }
}
