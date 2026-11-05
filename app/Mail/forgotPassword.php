<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class forgotPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {

        return $this->to($this->user->email)
            ->subject('Lupa Kata Sandi')
            ->view('emails.forgotPassword')
            ->with([
                'user' => $this->user,
                'link' => "https://ceritapanjikediri.my.id/#/forgot-password/" . encrypt($this->user->id)
            ]);
    }
}
