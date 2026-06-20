<?php

namespace App\Actions;

use App\Domain\Booking\Client;
use Illuminate\Support\Facades\DB;

class CreateClientAction
{
    public function execute(array $data, array $hotelIds = []): Client
    {
        if (! empty($data['contact_email'])) {
            $data['contact_email'] = mb_strtolower(trim($data['contact_email']));
        }

        return DB::transaction(function () use ($data, $hotelIds) {
            $client = Client::create($data);

            if ($hotelIds !== []) {
                $sync = collect($hotelIds)->mapWithKeys(fn ($hotelId) => [
                    $hotelId => [
                        'hotel_specific_code' => $client->external_id,
                        'status' => 'ACTIVE',
                        'metadata' => json_encode(['source' => 'client_create_action']),
                    ],
                ])->all();

                $client->hotels()->syncWithoutDetaching($sync);
            }

            return $client;
        });
    }
}
