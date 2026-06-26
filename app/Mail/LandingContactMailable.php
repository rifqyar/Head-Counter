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

        return $this
            ->to(config('app.contact_email', 'admin@rekayasadigital.com'))
            ->subject($subject)
            ->view('emails.landing-contact', ['data' => $this->payload]);
    }
}
