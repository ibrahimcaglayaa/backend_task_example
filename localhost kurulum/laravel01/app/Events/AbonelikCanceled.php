<?php
// app/Events/AbonelikStarted.php

namespace App\Events;

use App\Models\Endpoint;
use GuzzleHttp\Client;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbonelikCanceled
{

    use Dispatchable, SerializesModels;

    public $appID;
    public $deviceID;
    public $eventType;

    public function __construct($appID, $deviceID,$eventType)
    {
        $this->appID = $appID;
        $this->deviceID = $deviceID;
        $this->eventType = $eventType;

        $endpoint = Endpoint::first();

        if ($endpoint) {

            $endpointUrl = $endpoint->url;

            $client = new Client();

            $postData = [
                'appID' => $this->appID,
                'deviceID' => $this->deviceID,
                'eventType' => $this->eventType,
            ];

            $response = $client->post($endpointUrl, [
                'json' => $postData,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = $response->getBody()->getContents();

            Endpoint::where('id', '1')->update(['log' => $responseData]);
        }
    }


}
