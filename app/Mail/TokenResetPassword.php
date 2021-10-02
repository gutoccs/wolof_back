<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TokenResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $fullNameUser;
    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fullNameUser, $token)
    {
        $this->fullNameUser = $fullNameUser;
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
                        'fullNameUser'      =>  $this->fullNameUser,
                        'token'             =>  $this->token
                    ]);
    }
}
