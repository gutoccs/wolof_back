<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValidateAccount extends Mailable
{
    use Queueable, SerializesModels;

    protected $fullNameClient;
    protected $token;
    protected $type;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $fullNameClient, $token, $type)
    {
        $this->fullNameClient = $fullNameClient;
        $this->token = $token;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.validate_account')
        ->subject("Código para recuperar contraseña en Gabu App")
        ->with([
            'fullNameClient'    =>  $this->fullNameClient,
            'token'             =>  $this->token,
            'type'              =>  $this->type,
        ]);
    }
}
