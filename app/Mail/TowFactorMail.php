<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TowFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $firstName;

    public function __construct($code, $firstName)
    {
        $this->code = $code;
        $this->firstName = $firstName;
    }

    public function build()
    {
        return $this->view('otp')
                    ->subject('Two Factor Code')
                    ->with([
                        'code' => $this->code,
                        'firstName' => $this->firstName,
                    ]);
    }
}
