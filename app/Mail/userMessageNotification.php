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
    protected int $issueId;
    protected string $issueName;
    protected string $message;

    public function __construct(int $issueId, string $message)
    {
        $this->issueId = $issueId;
        //$this->issueName = $issueName;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.userMessageNotification')->subject('Notificación de mensaje del Centro de Servicios')
            ->with(['issueId' => $this->issueId, 'message' => $this->message]);
    }
}
