<?php

namespace App\Actions;

use App\Domain\Booking\Client;

class CreateClientAction
{
    public function execute(array $data): Client
    {
        if (! empty($data['contact_email'])) {
            $data['contact_email'] = mb_strtolower(trim($data['contact_email']));
        }

        return Client::create($data);
    }
}
