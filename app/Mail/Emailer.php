<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class Emailer extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $senderEmail;

    public function __construct($data, $senderEmail)
    {
        $this->data = $data;
        $this->senderEmail = $senderEmail;
    }

    public function build()
    {
        Log::info('Testing email logging');

        return $this->from($this->senderEmail)
            ->subject($this->data['subject'])
            ->view('emails.email', ['body' => $this->data['body'], 'subject' => $this->data['subject']])
            ->withSwiftMessage(function ($message) {
                \Log::channel('email')->info('Email sent', [
                    'to' => $message->getTo(),
                    'subject' => $message->getSubject(),
                ]);
            });
    }
}

?>