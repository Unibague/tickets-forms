<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserMessageNotificationEnhanced extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Notificación de mensaje del Centro de Servicios Solicitud ";
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('userMessageNotification2')->with('data', $this->data);
    }
}
