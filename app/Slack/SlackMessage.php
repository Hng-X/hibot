<?php

namespace Hibot\Slack;

use GuzzleHttp\Client;
use Hibot\Models\Credential;


class SlackMessage
{
    protected $channel;

    protected $text;

    protected $team;

    protected $asUser;

    public function __construct($teamId, $channelId, $text, $asUser = null)
    {
        $this->team = $teamId;
        $this->channel = $channelId;
        $this->text = $text;
        if($asUser)
            $this->asUser = $asUser;
        else
            $this->asUser = config("bot.as_user");
    }

    public static function sendWelcomeMessage($user, $team)
    {
        $welcomeMessages = config("bot.welcome.messages");
        $text = str_replace("@{user}", "<@$user>", $welcomeMessages[array_rand($welcomeMessages)]);
        $message = new SlackMessage($team, $user, $text);
        return $message->send();
    }

    public function send()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/chat.postMessage',

            array(

                'query' => [

                    'token' => Credential::where('team_id', $this->team)->first()->bot_access_token,

                    'channel' => $this->channel,

                    'text' => $this->text,
                    'as_user' => $this->asUser

                ]

            ));

        return json_decode($response->getBody(), true);
    }

    public function toArray()
    {
        return array(
            "team_id" => $this->team,
            "channel_id" => $this->channel,
            "text" => $this->text,
            "as_user" => $this->asUser
        );
    }
}