<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LandingContactMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payload,
    ) {}

    public function build(): static
    {
        $subject = match ($this->payload['type'] ?? 'contact') {
            'register' => '[Head Counter] New registration request - '.($this->payload['plan_label'] ?? 'Plan'),
            default => '[Head Counter] New contact inquiry',
        };

        $fromAddress = $this->payload['email'] ?? config('mail.from.address');
        $fromName = $this->payload['name'] ?? config('mail.from.name');

        return $this
            ->to(config('app.contact_email', 'admin@rekayasadigital.com'))
            ->from('admin@rekayasadigital.com', 'Rekayasa Digital - Head Counter App')
            ->replyTo($fromAddress, $fromName)
            ->subject($subject)
            ->view('emails.landing-contact', ['data' => $this->payload]);
    }
}
