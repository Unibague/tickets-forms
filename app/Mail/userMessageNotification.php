<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class userMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected int $ticket_id;
    protected string $message;

    public function __construct(int $ticket_id, string $message)
    {
        $this->ticket_id = $ticket_id;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.userMessageNotification')->subject('Notificacion del Centro de Servicios - Universidad de Ibagué')
            ->with(['ticket_id' => $this->ticket_id, 'message' => $this->message]);
    }
}
