<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TokenResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $fullNameClient;
    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fullNameClient, $token)
    {
        $this->fullNameClient = $fullNameClient;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.token_reset_password')
                    ->subject("Código para recuperar contraseña en Gabu App")
                    ->with([
                        'fullNameClient'    =>  $this->fullNameClient,
                        'token'             =>  $this->token
                    ]);
    }
}
