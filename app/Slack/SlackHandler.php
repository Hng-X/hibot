<?php

namespace App\Slack;


use App\Models\Credential;
use GuzzleHttp\Client;

class SlackHandler
{

    public static function dispatch(SlackMessage $message)
    {
        $data = $message->toArray();
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/chat.postMessage',
            array(
                'query' => [
                    'token' => Credential::where('team_id', $data['team_id'])->first()->bot_access_token,
                    'channel' => $data['channel_id'],
                    'text' => $data['text']
                ]
            ));
        return json_decode($response->getBody(), true);

    }
}