<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ValidatorEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $signedroute;

    public function __construct($signedroute)
    {
        $this->signedroute = $signedroute;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Activa tu cuenta'
        );
    }

    public function content()
    {
        return new Content(
            view: 'mails.validate',
        );
    }

    public function attachments()
    {
        return [];
    }
}
