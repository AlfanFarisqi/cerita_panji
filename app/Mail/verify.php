<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class verify extends Mailable
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
            ->subject('Verifikasi Akun Anda')
            ->view('emails.verify')
            ->with([
                'user' => $this->user,
                'link' => 'https://ceritapanjikediri.my.id/#/verify/' . encrypt($this->user->id)
            ]);
    }
}
