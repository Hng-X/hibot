<?php

namespace App\Slack;

use App\Models\Credential;

use GuzzleHttp\Client;


class SlackMessage
{
    protected $channel;

    protected $text;

    protected $team;

    protected $asUser;

    public function __construct($teamId, $channelId, $text, $asUser= true)
    {
        $this->team = $teamId;
        $this->channel = $channelId;
        $this->text = $text;
        $this->asUser = $asUser;
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
            "text" => $this->text
            "as_user" => $this->asUser
        );
    }

    public static function sendWelcomeMessage($user, $team)
    {
        $welcomeMessages = array(
            "Hey there, <@$user>! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
            "Hi, <@$user>! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
            "Welcome to the Hotels.ng remote internship Slack team, <@$user>!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
        );
        $message = new SlackMessage($team, $user, $welcomeMessages[array_rand($welcomeMessages)]);
        return $message->send();
    }
}